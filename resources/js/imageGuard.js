/*
 * Uploads over the server's limit used to kill the whole POST — a 502 with no
 * message. Catch it in the browser first: reject an oversized file before it is
 * ever sent, and tell the person why. The limit matches the `max:8192` rule
 * (8 MB) the server validates with.
 */
export const MAX_IMAGE_MB = 8;

/** True when a picked file is larger than the server will accept. */
export function tooLarge(file) {
    return !!file && file.size > MAX_IMAGE_MB * 1024 * 1024;
}

export const tooLargeMessage = `That image is too large. Please choose one under ${MAX_IMAGE_MB} MB.`;
