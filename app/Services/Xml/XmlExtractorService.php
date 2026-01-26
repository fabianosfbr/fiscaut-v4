<?php

namespace App\Services\Xml;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class XmlExtractorService
{
    /**
     * Processa o arquivo enviado e retorna uma coleção de conteúdos XML
     *
     * @param  UploadedFile  $file  Arquivo enviado
     * @return Collection Coleção de conteúdos XML
     */
    public function extract(UploadedFile $file): Collection
    {
        $xmlContents = collect();

        if ($file->getClientOriginalExtension() === 'zip') {
            $xmlContents = $this->extractFromZip($file);
        } elseif ($file->getClientOriginalExtension() === 'xml') {
            $xmlContents->push([
                'content' => $file->get(),
                'filename' => $file->getClientOriginalName(),
            ]);
        } else {
            throw new Exception('Formato de arquivo não suportado. Use XML ou ZIP.');
        }

        return $xmlContents;
    }

    /**
     * Processa o arquivo a partir de um caminho no sistema de arquivos
     *
     * @param  string  $filePath  Caminho do arquivo no sistema de arquivos
     * @return Collection Coleção de conteúdos XML
     */
    public function extractFromPath(string $filePath): Collection
    {
        if (! is_readable($filePath)) {
            throw new Exception("O arquivo não existe ou não pode ser lido: {$filePath}");
        }

        $xmlContents = collect();
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileName = basename($filePath);

        if ($fileExtension === 'zip') {
            $xmlContents = $this->extractFromZipPath($filePath);
        } elseif ($fileExtension === 'xml') {
            $xmlContents->push([
                'content' => file_get_contents($filePath),
                'filename' => $fileName,
            ]);
        } else {
            throw new Exception('Formato de arquivo não suportado. Use XML ou ZIP.');
        }

        return $xmlContents;
    }

    /**
     * Extrai os arquivos XML de um arquivo ZIP enviado como UploadedFile
     *
     * @param  UploadedFile  $zipFile  Arquivo ZIP enviado
     * @return Collection Coleção de conteúdos XML
     */
    private function extractFromZip(UploadedFile $zipFile): Collection
    {
        $xmlContents = collect();
        $zip = new ZipArchive;
        $tempPath = Storage::disk('local')->path('temp_'.uniqid().'.zip');

        try {
            // Move o arquivo para um local temporário
            file_put_contents($tempPath, $zipFile->get());

            if ($zip->open($tempPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Verifica se é um arquivo XML
                    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml') {
                        $xmlContents->push([
                            'content' => $zip->getFromIndex($i),
                            'filename' => $filename,
                        ]);
                    }
                }
                $zip->close();
            } else {
                throw new Exception('Não foi possível abrir o arquivo ZIP.');
            }

            // Remove o arquivo temporário
            unlink($tempPath);

            if ($xmlContents->isEmpty()) {
                throw new Exception('Nenhum arquivo XML encontrado no ZIP.');
            }

            return $xmlContents;

        } catch (Exception $e) {
            // Garante que o arquivo temporário seja removido em caso de erro
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Extrai os arquivos XML de um arquivo ZIP a partir de um caminho no sistema de arquivos
     *
     * @param  string  $zipFilePath  Caminho do arquivo ZIP no sistema de arquivos
     * @return Collection Coleção de conteúdos XML
     */
    private function extractFromZipPath(string $zipFilePath): Collection
    {
        $xmlContents = collect();
        $zip = new ZipArchive;

        try {
            if ($zip->open($zipFilePath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Verifica se é um arquivo XML
                    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml') {
                        $xmlContents->push([
                            'content' => $zip->getFromIndex($i),
                            'filename' => $filename,
                        ]);
                    }
                }
                $zip->close();
            } else {
                throw new Exception('Não foi possível abrir o arquivo ZIP.');
            }

            if ($xmlContents->isEmpty()) {
                throw new Exception('Nenhum arquivo XML encontrado no ZIP.');
            }

            return $xmlContents;

        } catch (Exception $e) {
            throw $e;
        }
    }
}
