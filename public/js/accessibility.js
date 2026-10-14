(() => {
    'use strict';
    const storageKey = 'entrelineas.accessibility';
    const sizes = ['normal', 'large', 'xlarge'];
    const themes = ['normal', 'contrast', 'dark', 'comfortable'];
    let preferences = { text: 'normal', theme: 'normal', voice: false };
    try {
        const saved = JSON.parse(localStorage.getItem(storageKey)) || {};
        if (sizes.includes(saved.text)) preferences.text = saved.text;
        if (themes.includes(saved.theme)) preferences.theme = saved.theme;
        preferences.voice = saved.voice === true;
    } catch (_) { /* El blog funciona también sin almacenamiento disponible. */ }

    function applyAppearance() {
        const root = document.documentElement;
        root.classList.remove(...sizes.map(size => `text-${size}`), ...themes.map(theme => `theme-${theme}`));
        root.classList.add(`text-${preferences.text}`, `theme-${preferences.theme}`);
    }
    applyAppearance();

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('accessibility-toggle');
        const panel = document.getElementById('accessibility-panel');
        const voiceButton = document.getElementById('voice-help');
        const voiceStatus = document.getElementById('voice-help-status');
        const preferenceStatus = document.getElementById('accessibility-status');
        const supported = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;
        const synth = supported ? window.speechSynthesis : null;
        const unsupported = 'La lectura por voz no está disponible en este navegador.';
        let articleUtterance = null;
        let helpUtterance = null;

        function save() {
            try { localStorage.setItem(storageKey, JSON.stringify(preferences)); }
            catch (_) { preferenceStatus.textContent = 'Preferencias aplicadas. Este navegador no permite guardarlas.'; }
        }
        function updateControls() {
            applyAppearance();
            panel.querySelectorAll('[data-text]').forEach(button => button.setAttribute('aria-pressed', button.dataset.text === preferences.text));
            panel.querySelectorAll('[data-theme]').forEach(button => button.setAttribute('aria-pressed', button.dataset.theme === preferences.theme));
            const sizeNames = ['Normal (100 %)', 'Grande (125 %)', 'Muy grande (150 %)'];
            document.getElementById('text-size-status').textContent = `Tamaño actual: ${sizeNames[sizes.indexOf(preferences.text)]}.`;
            document.getElementById('text-decrease').disabled = preferences.text === 'normal';
            document.getElementById('text-increase').disabled = preferences.text === 'xlarge';
            voiceButton.disabled = !supported;
            voiceButton.setAttribute('aria-pressed', preferences.voice);
            voiceButton.textContent = preferences.voice ? 'Desactivar ayuda por voz' : 'Activar ayuda por voz';
            voiceStatus.textContent = supported
                ? `Ayuda por voz ${preferences.voice ? 'activada' : 'desactivada'}. Todos los mensajes también aparecen en texto.`
                : unsupported;
        }
        function closePanel() {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
        toggle.disabled = false;
        toggle.addEventListener('click', () => {
            panel.hidden = !panel.hidden;
            toggle.setAttribute('aria-expanded', !panel.hidden);
        });
        document.getElementById('accessibility-close').addEventListener('click', closePanel);
        panel.addEventListener('keydown', event => {
            if (event.key === 'Escape') { event.preventDefault(); closePanel(); }
        });
        panel.querySelectorAll('[data-text], [data-theme]').forEach(button => {
            button.addEventListener('click', () => {
                if (button.dataset.text) preferences.text = button.dataset.text;
                if (button.dataset.theme) preferences.theme = button.dataset.theme;
                updateControls(); save();
            });
        });
        function resizeText(step) {
            preferences.text = sizes[Math.max(0, Math.min(2, sizes.indexOf(preferences.text) + step))];
            updateControls(); save();
        }
        document.getElementById('text-decrease').addEventListener('click', () => resizeText(-1));
        document.getElementById('text-increase').addEventListener('click', () => resizeText(1));

        function utterance(text) {
            const message = new SpeechSynthesisUtterance(text);
            message.lang = 'es-ES';
            const voices = synth.getVoices();
            const spanish = voices.find(voice => voice.lang === 'es-ES') || voices.find(voice => voice.lang.startsWith('es'));
            if (spanish) message.voice = spanish;
            return message;
        }
        function announce(text) {
            if (!supported || !preferences.voice || articleUtterance) return;
            if (helpUtterance) helpUtterance.onerror = null;
            synth.cancel();
            helpUtterance = utterance(text);
            helpUtterance.onerror = () => {
                voiceStatus.textContent = 'No se pudo reproducir la ayuda por voz. Todos los mensajes siguen disponibles en texto.';
            };
            synth.speak(helpUtterance);
        }
        voiceButton.addEventListener('click', () => {
            preferences.voice = !preferences.voice;
            if (!preferences.voice && helpUtterance && !articleUtterance) {
                helpUtterance.onerror = null;
                synth.cancel();
                helpUtterance = null;
            }
            updateControls(); save();
            if (preferences.voice) announce(voiceStatus.textContent);
        });
        document.getElementById('accessibility-reset').addEventListener('click', () => {
            preferences = { text: 'normal', theme: 'normal', voice: false };
            if (helpUtterance) helpUtterance.onerror = null;
            if (supported) stopReading();
            updateControls();
            try { localStorage.removeItem(storageKey); } catch (_) { /* Sin persistencia. */ }
            preferenceStatus.textContent = 'Preferencias restablecidas: texto normal, apariencia normal y ayuda por voz desactivada.';
        });

        const readerStatus = document.getElementById('reader-status');
        const start = document.getElementById('read-start');
        const pause = document.getElementById('read-pause');
        const resume = document.getElementById('read-resume');
        const stop = document.getElementById('read-stop');
        function readerState(state, text) {
            if (!readerStatus) return;
            readerStatus.textContent = text;
            start.disabled = !supported;
            pause.disabled = state !== 'speaking';
            resume.disabled = state !== 'paused';
            stop.disabled = !['speaking', 'paused', 'loading'].includes(state);
        }
        function stopReading() {
            articleUtterance = null; // Ignorar eventos de una lectura cancelada.
            synth.cancel();
            readerState('idle', 'Lectura detenida.');
        }
        if (start && supported) {
            readerState('idle', 'Lectura detenida. Pulsa Escuchar para comenzar.');
            start.addEventListener('click', () => {
                stopReading();
                if (helpUtterance) helpUtterance.onerror = null;
                const text = ['[data-news-title]', '[data-news-summary]', '[data-news-content]']
                    .map(selector => document.querySelector(selector).innerText.trim()).join('\n\n');
                const message = utterance(text);
                articleUtterance = message;
                readerState('loading', 'Preparando la lectura.');
                message.onstart = () => { if (articleUtterance === message) readerState('speaking', 'Lectura en curso.'); };
                message.onend = () => {
                    if (articleUtterance !== message) return;
                    articleUtterance = null;
                    readerState('idle', 'Lectura finalizada.');
                };
                message.onerror = () => {
                    if (articleUtterance !== message) return;
                    articleUtterance = null;
                    readerState('idle', 'No se pudo reproducir la noticia. El contenido sigue disponible en texto.');
                };
                synth.speak(message);
            });
            pause.addEventListener('click', () => { synth.pause(); readerState('paused', 'Lectura pausada.'); });
            resume.addEventListener('click', () => { synth.resume(); readerState('speaking', 'Lectura reanudada.'); });
            stop.addEventListener('click', stopReading);
        } else if (readerStatus) readerState('idle', unsupported);

        updateControls();
        const summary = document.getElementById('error-summary');
        if (summary) summary.focus();
        // Una única lectura breve al cargar: errores o confirmación, nunca toda la interfaz.
        const notification = document.querySelector('[data-voice-message]');
        if (notification) announce(notification.textContent.trim());
        window.addEventListener('pagehide', () => {
            articleUtterance = null;
            if (helpUtterance) helpUtterance.onerror = null;
            if (supported) synth.cancel();
        });
    });
})();
