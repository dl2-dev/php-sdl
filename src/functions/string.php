<?php declare(strict_types=1);

namespace DL2\SDL\String;

function unaccent(string $str): string
{
    return transliterator_transliterate(
        'NFD; Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove; NFC;',
        $str
    );
}
