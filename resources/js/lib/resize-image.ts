export async function resizeImage(file: File, maxSize = 1920, quality = 0.85): Promise<File> {
    if (!file.type.startsWith('image/')) return file;

    try {
        const bitmap = await createImageBitmap(file);
        const { width, height } = bitmap;
        const ratio = Math.min(maxSize / width, maxSize / height, 1);

        if (ratio === 1) {
            bitmap.close?.();
            return file;
        }

        const w = Math.round(width * ratio);
        const h = Math.round(height * ratio);

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            bitmap.close?.();
            return file;
        }
        ctx.drawImage(bitmap, 0, 0, w, h);
        bitmap.close?.();

        const outType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        const blob: Blob | null = await new Promise((res) => canvas.toBlob(res, outType, quality));
        if (!blob) return file;

        const ext = outType === 'image/png' ? 'png' : 'jpg';
        const base = file.name.replace(/\.[^.]+$/, '');
        return new File([blob], `${base}.${ext}`, { type: outType, lastModified: Date.now() });
    } catch {
        return file;
    }
}
