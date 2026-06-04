export function ucfirst(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

export function isPlainObject(value) {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
