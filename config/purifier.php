<?php
/**
 * Ok, HTML Purifier Configuration.
 *
 * @link https://github.com/mewebstudio/Purifier
 */

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'ignoreNonStrings' => false,
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0752,
    'settings'      => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title|target],ul,ol,li,p[style|class],br,span[style|class],img[width|height|alt|src|style],math[xmlns],semantics,mrow,mi,mn,mo,mfrac,msup,msub,annotation[encoding],table,tbody,tr,td,th,h1,h2,h3,h4,h5,h6,blockquote,pre,code,iframe[src|width|height|frameborder|allowfullscreen|style|class]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,padding-right,width,height',
            'HTML.SafeIframe'          => true,
            'URI.SafeIframeRegexp'     => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/|www.youtube-nocookie.com/embed/)%',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
        ],
        'test'    => [
            'Attr.EnableID' => 'true',
        ],
        "youtube" => [
            "HTML.SafeIframe"      => 'true',
            "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
        ],
        'custom_definition' => [
            'id'  => 'html5-definitions',
            'rev' => 1,
            'setup' => function ($def) {
                $def->addElement('math', 'Block', 'Flow', 'Common', ['xmlns' => 'URI']);
                $def->addElement('semantics', 'Block', 'Flow', 'Common');
                $def->addElement('mrow', 'Inline', 'Inline', 'Common');
                $def->addElement('mi', 'Inline', 'Inline', 'Common');
                $def->addElement('mn', 'Inline', 'Inline', 'Common');
                $def->addElement('mo', 'Inline', 'Inline', 'Common');
                $def->addElement('mfrac', 'Inline', 'Inline', 'Common');
                $def->addElement('msup', 'Inline', 'Inline', 'Common');
                $def->addElement('msub', 'Inline', 'Inline', 'Common');
                $def->addElement('annotation', 'Inline', 'Inline', 'Common', ['encoding' => 'Text']);
                $def->addAttribute('span', 'class', 'Text');
                $def->addAttribute('a', 'target', 'Text');
            },
        ],
        'custom_attributes' => [
            ['a', 'target', 'Text'],
            ['span', 'class', 'Text'],
        ],
        'custom_elements' => [
            ['math', 'Block', 'Flow', 'Common', ['xmlns' => 'URI']],
            ['semantics', 'Block', 'Flow', 'Common'],
            ['mrow', 'Inline', 'Inline', 'Common'],
            ['mi', 'Inline', 'Inline', 'Common'],
            ['mn', 'Inline', 'Inline', 'Common'],
            ['mo', 'Inline', 'Inline', 'Common'],
            ['mfrac', 'Inline', 'Inline', 'Common'],
            ['msup', 'Inline', 'Inline', 'Common'],
            ['msub', 'Inline', 'Inline', 'Common'],
            ['annotation', 'Inline', 'Inline', 'Common', ['encoding' => 'Text']],
        ],
    ],
];
