const LABEL = 'copy';
const RESET_DELAY_MS = 1500;

export function addCopyButtons(root: ParentNode): void {
    if (!navigator.clipboard) {
        return;
    }

    root.querySelectorAll<HTMLPreElement>('.prose pre').forEach((pre) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'copy-code';
        button.textContent = LABEL;
        button.setAttribute('aria-label', 'Copy code');

        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(pre.querySelector('code')?.innerText ?? pre.innerText);
                button.textContent = 'copied!';
            } catch {
                button.textContent = 'error';
            }
            setTimeout(() => { button.textContent = LABEL; }, RESET_DELAY_MS);
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'code-block';
        if (pre.dataset.language) {
            wrapper.dataset.language = pre.dataset.language;
        }
        pre.replaceWith(wrapper);
        wrapper.append(pre, button);
    });
}
