<?php namespace Notion\Blocks;

use Notion\BlockBase;

class ImageBlock extends BlockBase
{
    public $type = 'image';

    public function __construct(string $image_url)
    {
        $this->typeConfiguration = [
            'type' => 'external',
            'external' => [
                'url' => $image_url,
            ],
        ];
    }
}
