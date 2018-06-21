<?php

namespace Chemem\DumbFlower\State;

const DEFAULT_IMG_CONTRAST = [1];

const DEFAULT_IMG_BRIGHTNESS = [0];

const DEFAULT_SMOOTH_FILTER = [100];

const DEFAULT_IMG_PIXELATE = [3, true];

const DEFAULT_RED_FILTER = [255, 0, 0];

const DEFAULT_BLUE_FILTER = [0, 0, 255];

const DEFAULT_GREEN_FILTER = [0, 255, 0];

const CONSOLE_COMMANDS = [
    'brightness' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => DEFAULT_IMG_BRIGHTNESS
    ],
    'smoothen' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => DEFAULT_SMOOTH_FILTER
    ],
    'negate' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => []
    ],
    'grayscale' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => []
    ],
    'colorize' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => [DEFAULT_RED_FILTER, DEFAULT_GREEN_FILTER, DEFAULT_BLUE_FILTER]
    ],
    'contrast' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => DEFAULT_IMG_CONTRAST
    ],
    'emboss' => [
        'type' => 'filter',
        'hasDefault' => true,
        'default' => []
    ],
    'resize' => [
        'type' => 'resize',
        'hasDefault' => false,
        'default' => null
    ],
    'help' => [
        'type' => 'help',
        'hasDefault' => false,
        'default' => null
    ]
];

const CONSOLE_INTRO_TXT = 'DumbFlower Console';

const CONSOLE_INTRO_HELP = 'Format: <command> <args> <sourcefile>';

const CONSOLE_INTRO_AUTHOR = 'Designed by Lochemem Bruno Michael';

const CONSOLE_INTRO_CMD_DESC = '<command> The action to be performed eg. smoothen, brightness, resize';

const CONSOLE_INTRO_ARG_DESC = '<args> The array of arguments for the command eg. color profiles [255, 0, 0] for red';

const CONSOLE_INTRO_SRC_DESC = '<sourcefile> The file or directory on which to perform the operation eg. --s=file.png';