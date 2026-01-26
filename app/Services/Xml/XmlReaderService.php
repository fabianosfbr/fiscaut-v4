<?php

namespace App\Services\Xml;

use Exception;
use DOMElement;
use SimpleXMLElement;

class XmlReaderService
{
    public function read(string $xml): array
    {
        $xml = trim($xml);

        if ($xml === '') {
            throw new Exception('XML vazio.');
        }

        $previousUseInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $simpleXml = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);

            if ($simpleXml === false) {
                $errors = libxml_get_errors();
                $first = $errors[0] ?? null;

                $message = 'XML inválido.';
                if ($first !== null) {
                    $message = trim($first->message);
                }

                throw new Exception('Erro ao ler XML: '.$message);
            }

            $domElement = dom_import_simplexml($simpleXml);

            if (! $domElement instanceof DOMElement) {
                throw new Exception('Erro ao converter XML para DOM.');
            }

            $rootName = $domElement->localName ?: $domElement->nodeName;

            return [
                $rootName => $this->domElementToArray($domElement),
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
    }

    private function domElementToArray(DOMElement $element): array|string
    {
        $hasElementChildren = false;
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $hasElementChildren = true;
                break;
            }
        }

        $attributes = [];
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attribute) {
                $attributes[$attribute->nodeName] = $attribute->nodeValue ?? '';
            }
        }

        if (! $hasElementChildren) {
            $text = trim($element->textContent ?? '');

            if ($attributes === []) {
                return $text;
            }

            $result = [
                '@attributes' => $attributes,
            ];

            if ($text !== '') {
                $result['@content'] = $text;
            }

            return $result;
        }

        $result = [];

        if ($attributes !== []) {
            $result['@attributes'] = $attributes;
        }

        foreach ($element->childNodes as $childNode) {
            if (! $childNode instanceof DOMElement) {
                continue;
            }

            $childName = $childNode->localName ?: $childNode->nodeName;
            $childValue = $this->domElementToArray($childNode);

            if (! array_key_exists($childName, $result)) {
                $result[$childName] = $childValue;
                continue;
            }

            if (! is_array($result[$childName]) || $this->isAssoc($result[$childName])) {
                $result[$childName] = [$result[$childName]];
            }

            $result[$childName][] = $childValue;
        }

        return $result;
    }

    private function isAssoc(array $value): bool
    {
        $keys = array_keys($value);

        return $keys !== range(0, count($keys) - 1);
    }
}
