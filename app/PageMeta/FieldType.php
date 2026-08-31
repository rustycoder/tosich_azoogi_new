<?php

namespace App\PageMeta;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Html = 'html';
    case Url = 'url';
    case Image = 'image';
    case Video = 'video';
    case Select = 'select';
}
