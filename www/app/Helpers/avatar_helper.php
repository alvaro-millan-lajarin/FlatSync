<?php

/**
 * Renders a user avatar: image if available, else colored initials circle.
 *
 * @param string|null $avatarUrl  Relative path stored in DB (e.g. "uploads/avatars/x.jpg")
 * @param string      $username   Used for initials fallback
 * @param int         $size       Pixel size of the circle
 * @param string      $extra      Extra inline CSS
 */
function avatar(?string $avatarUrl, string $username, int $size = 32, string $extra = ''): string
{
    $initials = strtoupper(mb_substr($username, 0, 1));
    $fontSize = (int) ($size * 0.4);
    $base     = base_url();

    if ($avatarUrl) {
        return sprintf(
            '<img src="%s%s" alt="%s" style="width:%dpx;height:%dpx;border-radius:50%%;object-fit:cover;flex-shrink:0;%s">',
            rtrim($base, '/') . '/', htmlspecialchars($avatarUrl, ENT_QUOTES),
            htmlspecialchars($initials, ENT_QUOTES),
            $size, $size, $extra
        );
    }

    return sprintf(
        '<div style="width:%dpx;height:%dpx;border-radius:50%%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:%dpx;font-weight:700;color:#fff;flex-shrink:0;%s">%s</div>',
        $size, $size, $fontSize, $extra, $initials
    );
}
