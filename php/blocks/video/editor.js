/**
 * editor.js - Lyquix Video block editor UI
 *
 * Edit function for the lqx/video block. Renders InspectorControls panels for
 * Video Source and Playback, and a custom preview in the editor body.
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//   "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

(function () {
    const { registerBlockType } = wp.blocks;
    const { Fragment, createElement: el } = wp.element;
    const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
    const { PanelBody, ToggleControl, TextControl, SelectControl, Button, Placeholder } = wp.components;

    // Derive a thumbnail URL from a YouTube/Vimeo URL (for the editor preview only)
    function thumbnailFromUrl(url) {
        if (!url) return null;
        let m = url.match(/(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/|live\/|watch\?v=|&v=)([^#&?]+)/);
        if (m && m[1]) return 'https://img.youtube.com/vi/' + m[1] + '/hqdefault.jpg';
        m = url.match(/vimeo\.com\/(?:channels\/\w+\/|groups\/[^/]*\/videos\/|video\/)?(\d+)/);
        if (m && m[1]) return 'https://vumbnail.com/' + m[1] + '.jpg';
        return null;
    }

    // Reusable media picker — returns a block to insert as a child
    function mediaPicker(label, type, currentId, currentUrl, onSelect, onRemove) {
        return el('div', { style: { marginBottom: '16px' } },
            el('label', { style: { display: 'block', fontWeight: 600, marginBottom: '8px' } }, label),
            el(MediaUploadCheck, {},
                el(MediaUpload, {
                    onSelect: onSelect,
                    allowedTypes: [type],
                    value: currentId || 0,
                    render: function (p) {
                        const has = !!currentUrl;
                        return el(Fragment, {},
                            has && type === 'image' && el('img', {
                                src: currentUrl, alt: '',
                                style: { display: 'block', maxWidth: '100%', marginBottom: '8px', border: '1px solid #ddd' }
                            }),
                            has && type === 'video' && el('p', {
                                style: { fontSize: '12px', wordBreak: 'break-all', margin: '0 0 8px' }
                            }, currentUrl),
                            el(Button, { onClick: p.open, variant: 'secondary' },
                                has ? ('Replace ' + label) : ('Select ' + label)),
                            has && el(Button, {
                                onClick: onRemove,
                                variant: 'link',
                                isDestructive: true,
                                style: { marginLeft: '8px' }
                            }, 'Remove')
                        );
                    }
                })
            )
        );
    }

    registerBlockType('lqx/video', {
        edit: function (props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            const isUrl = attributes.videoType === 'url';
            const isUpload = attributes.videoType === 'upload';
            const isLyqbox = isUrl || (isUpload && attributes.openInLyqbox);

            const previewImage = attributes.posterUrl ||
                (isUrl ? thumbnailFromUrl(attributes.videoUrl) : null);

            // === Source panel ===
            const sourcePanel = el(PanelBody, { title: 'Video Source', initialOpen: true },
                el(SelectControl, {
                    label: 'Video Type',
                    value: attributes.videoType,
                    options: [
                        { label: 'URL (YouTube/Vimeo)', value: 'url' },
                        { label: 'Upload', value: 'upload' }
                    ],
                    onChange: function (val) { setAttributes({ videoType: val }); }
                }),
                isUrl && el(TextControl, {
                    label: 'Video URL',
                    help: 'Paste a YouTube or Vimeo URL.',
                    value: attributes.videoUrl,
                    onChange: function (val) { setAttributes({ videoUrl: val }); }
                }),
                isUpload && mediaPicker('Video File', 'video',
                    attributes.videoUploadId, attributes.videoUploadUrl,
                    function (media) {
                        setAttributes({
                            videoUploadId: media.id,
                            videoUploadUrl: media.url,
                            videoUploadMime: media.mime || ''
                        });
                    },
                    function () {
                        setAttributes({ videoUploadId: 0, videoUploadUrl: '', videoUploadMime: '' });
                    }
                ),
                mediaPicker('Poster Image', 'image',
                    attributes.posterId, attributes.posterUrl,
                    function (media) { setAttributes({ posterId: media.id, posterUrl: media.url }); },
                    function () { setAttributes({ posterId: 0, posterUrl: '' }); }
                ),
                el(TextControl, {
                    label: 'Caption',
                    value: attributes.caption,
                    onChange: function (val) { setAttributes({ caption: val }); }
                })
            );

            // === Playback panel (Upload only) ===
            const playbackPanel = isUpload && el(PanelBody, { title: 'Playback', initialOpen: false },
                el(ToggleControl, {
                    label: 'Open in Lyqbox',
                    help: 'Show as a poster image and open the video in a Lyqbox modal on click. When enabled, the inline player toggles below are not used.',
                    checked: !!attributes.openInLyqbox,
                    onChange: function (val) { setAttributes({ openInLyqbox: val }); }
                }),
                !attributes.openInLyqbox && el(Fragment, {},
                    el(ToggleControl, {
                        label: 'Show Controls',
                        checked: !!attributes.controls,
                        onChange: function (val) { setAttributes({ controls: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Autoplay',
                        checked: !!attributes.autoplay,
                        onChange: function (val) { setAttributes({ autoplay: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Loop',
                        checked: !!attributes.loop,
                        onChange: function (val) { setAttributes({ loop: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Muted',
                        checked: !!attributes.muted,
                        onChange: function (val) { setAttributes({ muted: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Lazy Load',
                        help: 'Load the video file only when it nears the viewport.',
                        checked: !!attributes.lazyLoad,
                        onChange: function (val) { setAttributes({ lazyLoad: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Play on Hover',
                        help: 'Play on mouse enter, pause and reset on leave.',
                        checked: !!attributes.hoverPlay,
                        onChange: function (val) { setAttributes({ hoverPlay: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Play in Viewport',
                        help: 'Play while the video is in the viewport.',
                        checked: !!attributes.viewportPlay,
                        onChange: function (val) { setAttributes({ viewportPlay: val }); }
                    })
                )
            );

            // === Editor preview ===
            const hasSource = (isUrl && attributes.videoUrl) || (isUpload && attributes.videoUploadUrl);

            const preview = !hasSource
                ? el(Placeholder, {
                    icon: 'format-video',
                    label: 'Lyquix Video',
                    instructions: 'Configure the video source in the block settings panel.'
                })
                : el('div', {
                    className: 'lqx-video-editor-preview',
                    style: { position: 'relative', backgroundColor: '#000', minHeight: '200px', overflow: 'hidden' }
                },
                    previewImage && el('img', {
                        src: previewImage, alt: '',
                        style: { display: 'block', width: '100%', height: 'auto' }
                    }),
                    !previewImage && el('div', {
                        style: { padding: '40px 20px', color: '#fff', textAlign: 'center', fontSize: '13px', wordBreak: 'break-all' }
                    }, isUpload ? attributes.videoUploadUrl : attributes.videoUrl),
                    isLyqbox && el('div', {
                        style: {
                            position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
                            width: '64px', height: '64px', borderRadius: '50%',
                            backgroundColor: 'rgba(0,0,0,0.7)', color: '#fff',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            fontSize: '24px', pointerEvents: 'none'
                        }
                    }, '▶'),
                    attributes.caption && el('div', {
                        style: { padding: '8px 12px', backgroundColor: '#222', color: '#ccc', fontSize: '14px' }
                    }, attributes.caption)
                );

            return el(Fragment, {},
                el(InspectorControls, {}, sourcePanel, playbackPanel),
                el('div', blockProps, preview)
            );
        },
        save: function () { return null; }
    });
})();
