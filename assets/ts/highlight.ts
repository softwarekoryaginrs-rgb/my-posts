import type { LanguageFn } from 'highlight.js';
import hljs from 'highlight.js/lib/core';
import bash from 'highlight.js/lib/languages/bash';
import css from 'highlight.js/lib/languages/css';
import diff from 'highlight.js/lib/languages/diff';
import dockerfile from 'highlight.js/lib/languages/dockerfile';
import go from 'highlight.js/lib/languages/go';
import ini from 'highlight.js/lib/languages/ini';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import markdown from 'highlight.js/lib/languages/markdown';
import nginx from 'highlight.js/lib/languages/nginx';
import php from 'highlight.js/lib/languages/php';
import plaintext from 'highlight.js/lib/languages/plaintext';
import python from 'highlight.js/lib/languages/python';
import rust from 'highlight.js/lib/languages/rust';
import shell from 'highlight.js/lib/languages/shell';
import sql from 'highlight.js/lib/languages/sql';
import twig from 'highlight.js/lib/languages/twig';
import typescript from 'highlight.js/lib/languages/typescript';
import xml from 'highlight.js/lib/languages/xml';
import yaml from 'highlight.js/lib/languages/yaml';

const languages: Record<string, LanguageFn> = {
    bash, css, diff, dockerfile, go, ini, javascript, json, markdown, nginx, php,
    plaintext, python, rust, shell, sql, twig, typescript, xml, yaml,
};

const aliases: Record<string, string[]> = {
    bash: ['sh', 'zsh', 'console'],
    javascript: ['js', 'jsx', 'mjs'],
    typescript: ['ts', 'tsx'],
    xml: ['html', 'svg'],
    yaml: ['yml'],
    ini: ['toml', 'env'],
    plaintext: ['text', 'txt'],
};

for (const [name, language] of Object.entries(languages)) {
    hljs.registerLanguage(name, language);
}

for (const [languageName, names] of Object.entries(aliases)) {
    hljs.registerAliases(names, { languageName });
}

const LANGUAGE_PREFIX = 'language-';

export function highlightCodeBlocks(root: ParentNode): void {
    root.querySelectorAll<HTMLElement>('.prose pre > code').forEach((block) => {
        const language = [...block.classList]
            .find((cls) => cls.startsWith(LANGUAGE_PREFIX))
            ?.slice(LANGUAGE_PREFIX.length);

        if (!language) {
            return;
        }

        const pre = block.closest('pre');
        if (pre) {
            pre.dataset.language = language;
        }

        if (hljs.getLanguage(language)) {
            hljs.highlightElement(block);
        }
    });
}
