const files = import.meta.glob('/lang/spectacular/*.json', {
    eager: true,
    import: 'default',
});

const messages = Object.fromEntries(Object.entries(files).map(([path, translations]) => {
    const locale = path.split('/').pop().replace('.json', '');

    return [locale, translations];
}));

export const locales = Object.keys(messages).map(locale => ({
    id: locale,
    name: new Intl.DisplayNames('en', { type: 'language' }).of(locale),
}));

export function translate(key, locale) {
    return messages[locale]?.[key] ?? messages.en?.[key] ?? key;
}

export default {
    install(app) {
        app.config.globalProperties.$t = translate;
    },
};
