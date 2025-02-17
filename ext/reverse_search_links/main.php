<?php

declare(strict_types=1);

namespace Shimmie2;

abstract class ReverseSearchLinksConfig
{
    public const ENABLED_SERVICES = "ext_reverse_search_links_enabled_services";
}

class ReverseSearchLinks extends Extension
{
    /** @var ReverseSearchLinksTheme */
    protected Themelet $theme;

    /**
     * Show the extension block when viewing an image
     */
    public function onDisplayingImage(DisplayingImageEvent $event)
    {
        global $page;

        // only support image types
        $supported_types = [MimeType::JPEG, MimeType::GIF, MimeType::PNG, MimeType::WEBP];
        if(in_array($event->image->get_mime(), $supported_types)) {
            $this->theme->reverse_search_block($page, $event->image);
        }
    }


    /**
     * Supported reverse search services
     */
    protected array $SERVICES = [
        'SauceNAO',
        'TinEye',
        'trace.moe',
        'ascii2d',
        'Yandex'
    ];

    private function get_options(): array
    {
        global $config;

        $output = [];
        $services = $this->SERVICES;
        foreach ($services as $service) {
            $output[$service] = $service;
        }

        return $output;
    }

    /**
     * Set default config values
     */
    public function onInitExt(InitExtEvent $event)
    {
        global $config;
        $config->set_default_array(
            ReverseSearchLinksConfig::ENABLED_SERVICES,
            ['SauceNAO', 'TinEye', 'trace.moe', 'ascii2d', 'Yandex']
        );
    }
}
