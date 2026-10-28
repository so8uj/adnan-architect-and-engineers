/*
 * Turns any pasted video link into something the player modal can show.
 *   YouTube (watch / youtu.be / shorts / embed), Facebook video URL, or a direct .mp4/.webm file (e.g. assets/video/intro.mp4)
 * Returns { kind: 'iframe' | 'file', src } or null when the link is empty / unknown.
 */
window.parseVideo = function (url) {
  if (!url) return null;
  const yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/))([\w-]{11})/);
  if (yt) return { kind: 'iframe', src: 'https://www.youtube-nocookie.com/embed/' + yt[1] + '?autoplay=1&rel=0&modestbranding=1' };
  if (/facebook\.com|fb\.watch/.test(url))
    return { kind: 'iframe', src: 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=true&href=' + encodeURIComponent(url) };
  if (/\.(mp4|webm|ogg)(\?.*)?$/i.test(url)) return { kind: 'file', src: url };
  return null;
};
