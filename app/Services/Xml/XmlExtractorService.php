<?php

namespace App\Services\Xml;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

            $result = $zip->open($tempPath);
            if ($result === true) {
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
                
                // Properly close the zip archive with error checking
                $closeResult = $zip->close();
                if (!$closeResult) {
                    Log::error('Failed to close zip archive in extractFromZip', [
                        'temp_path' => $tempPath,
                        'service_class' => self::class
                    ]);
                    
                    throw new Exception('Could not close zip file properly');
                }
            } else {
                Log::error('Failed to open zip archive in extractFromZip', [
                    'result_code' => $result,
                    'temp_path' => $tempPath,
                    'service_class' => self::class
                ]);
                
                throw new Exception("Não foi possível abrir o arquivo ZIP. Código de erro: {$result}");
            }

            // Remove o arquivo temporário
            unlink($tempPath);

            if ($xmlContents->isEmpty()) {
                throw new Exception('Nenhum arquivo XML encontrado no ZIP.');
            }

            return $xmlContents;

        } catch (Exception $e) {
            Log::error('Error in extractFromZip method', [
                'error' => $e->getMessage(),
                'temp_path' => $tempPath,
                'service_class' => self::class
            ]);
            
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
            $result = $zip->open($zipFilePath);
            if ($result === true) {
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
                
                // Properly close the zip archive with error checking
                $closeResult = $zip->close();
                if (!$closeResult) {
                    Log::error('Failed to close zip archive in extractFromZipPath', [
                        'zip_file_path' => $zipFilePath,
                        'service_class' => self::class
                    ]);
                    
                    throw new Exception('Could not close zip file properly');
                }
            } else {
                Log::error('Failed to open zip archive in extractFromZipPath', [
                    'result_code' => $result,
                    'zip_file_path' => $zipFilePath,
                    'service_class' => self::class
                ]);
                
                throw new Exception("Não foi possível abrir o arquivo ZIP. Código de erro: {$result}");
            }

            if ($xmlContents->isEmpty()) {
                throw new Exception('Nenhum arquivo XML encontrado no ZIP.');
            }

            return $xmlContents;

        } catch (Exception $e) {
            Log::error('Error in extractFromZipPath method', [
                'error' => $e->getMessage(),
                'zip_file_path' => $zipFilePath,
                'service_class' => self::class,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
