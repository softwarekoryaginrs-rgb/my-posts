import path from 'node:path';
import { defineConfig } from '@rspack/cli';
import { RspackManifestPlugin } from 'rspack-manifest-plugin';

// Assets are emitted to public/build and referenced from Twig via asset('build/...'),
// which Symfony resolves through public/build/manifest.json (see the "build" package in config/packages/framework.yaml).
export default defineConfig((env, argv) => {
    const isProd = argv.mode === 'production';

    return {
        entry: {
            app: './assets/app.ts',
        },
        output: {
            path: path.resolve(import.meta.dirname, 'public/build'),
            publicPath: '/build/',
            filename: isProd ? '[name].[contenthash:8].js' : '[name].js',
            cssFilename: isProd ? '[name].[contenthash:8].css' : '[name].css',
            assetModuleFilename: 'assets/[name].[contenthash:8][ext]',
            clean: true,
        },
        resolve: {
            extensions: ['.ts', '.js'],
        },
        module: {
            rules: [
                {
                    test: /\.ts$/,
                    exclude: /node_modules/,
                    loader: 'builtin:swc-loader',
                    options: { detectSyntax: 'auto' },
                    type: 'javascript/auto',
                },
                { test: /\.css$/, type: 'css' },
                {
                    test: /\.scss$/,
                    // sass-loader picks up sass-embedded from package.json automatically.
                    use: [{ loader: 'sass-loader', options: { sourceMap: !isProd } }],
                    type: 'css',
                },
                { test: /\.(woff2?|ttf|eot|svg|png|jpe?g|gif|webp)$/, type: 'asset/resource' },
            ],
        },
        plugins: [
            new RspackManifestPlugin({
                basePath: 'build/',
                publicPath: '/build/',
            }),
        ],
        devtool: isProd ? false : 'source-map',
    };
});
