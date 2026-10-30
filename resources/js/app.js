// Livewire bundles and starts Alpine.js; behaviour lives in small Alpine components inside Blade views.
document.addEventListener('alpine:init', () => {
    window.Alpine.store('theme', {
        current: document.documentElement.dataset.theme,
        toggle() {
            this.current = this.current === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = this.current;
            try {
                localStorage.setItem('theme', this.current);
            } catch (e) {}
        },
    });
});
