import '@fontsource-variable/montserrat';
import '@fontsource/merriweather/400.css';
import '@fontsource/merriweather/400-italic.css';
import '@fontsource/merriweather/700.css';
import '@fontsource-variable/jetbrains-mono';
import './styles/app.scss';

import { highlightCodeBlocks } from './ts/highlight';
import { addCopyButtons } from './ts/copy-code';

document.addEventListener('DOMContentLoaded', () => {
    highlightCodeBlocks(document);
    addCopyButtons(document);
});
