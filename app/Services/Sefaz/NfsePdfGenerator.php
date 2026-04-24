<?php

namespace App\Services\Sefaz;

use TCPDF;

class NfsePdfGenerator
{
    private $pdf;

    private $data;

    private $margin = 5;

    private $logoSvg = null;

    private $headerInfo = [
        'municipalityLine' => null,
        'secretariatLine' => null,
        'phoneLine' => null,
        'emailLine' => null,
    ];

    public function __construct()
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('NFS-e PDF Generator');
        $this->pdf->SetAuthor('NFS-e System');
        $this->pdf->SetTitle('DANFSe');
        $this->pdf->SetSubject('Documento Auxiliar da NFS-e');
        $this->pdf->SetMargins($this->margin, $this->margin, $this->margin);
        $this->pdf->SetAutoPageBreak(true, $this->margin);
        $this->pdf->SetFont('helvetica', '', 8);
    }

    /**
     * Set SVG content for the logo rendered in the top-left
     * corner of the header. Pass the raw SVG XML string.
     *
     * Example:
     *  $svg = file_get_contents('BrasaoCriciuma.svg');
     *  $generator->setLogoSvg($svg);
     */
    public function setLogoSvg(string $svgContent)
    {
        $this->logoSvg = $svgContent;

        return $this;
    }

    public function setHeaderInfo(array $headerInfo)
    {
        $this->headerInfo = array_merge($this->headerInfo, $headerInfo);

        return $this;
    }

    public function parseXml($xmlInput)
    {
        $xml = $this->loadXml($xmlInput);
        if ($xml === false) {
            throw new \Exception('Failed to parse XML file');
        }

        $ns = $xml->getNamespaces(true);
        $infNFSe = $xml->children($ns[''])->infNFSe;
        $dps = $infNFSe->children($ns[''])->DPS->children($ns[''])->infDPS;
        $tomaEndereco = $dps->toma->end;
        $totaisTributos = $dps->valores->trib->totTrib->vTotTrib;

        // Extract Id attribute using attributes() method
        $id = (string) $infNFSe->attributes()->Id;
        // Chave de acesso is the Id value without "NFS" prefix for display
        $chaveAcesso = preg_replace('/^NFS/', '', $id);

        $this->data = [
            'chaveAcesso' => $chaveAcesso,
            'numeroNfse' => (string) $infNFSe->nNFSe,
            'localEmissao' => (string) $infNFSe->xLocEmi,
            'localPrestacao' => (string) $infNFSe->xLocPrestacao,
            'localIncidencia' => (string) $infNFSe->xLocIncid,
            'tribNac' => (string) $infNFSe->xTribNac,
            'dataProcessamento' => $this->formatDateTime((string) $infNFSe->dhProc),
            'numeroDFSe' => (string) $infNFSe->nDFSe,
            'emitente' => [
                'cnpj' => $this->formatCnpjCpf((string) $infNFSe->emit->CNPJ),
                'im' => (string) $infNFSe->emit->IM,
                'nome' => (string) $infNFSe->emit->xNome,
                'logradouro' => (string) $infNFSe->emit->enderNac->xLgr,
                'numero' => (string) $infNFSe->emit->enderNac->nro,
                'bairro' => (string) $infNFSe->emit->enderNac->xBairro,
                'municipio' => (string) $infNFSe->emit->enderNac->cMun,
                'uf' => (string) $infNFSe->emit->enderNac->UF,
                'cep' => $this->formatCep((string) $infNFSe->emit->enderNac->CEP),
                'fone' => $this->formatPhone((string) $infNFSe->emit->fone),
                'email' => (string) $infNFSe->emit->email,
            ],
            'tomador' => [
                'cnpj' => $this->formatCnpjCpf((string) $dps->toma->CNPJ),
                'nome' => (string) $dps->toma->xNome,
                'logradouro' => $this->readFirstValue([
                    $tomaEndereco->xLgr,
                    $tomaEndereco->endNac->xLgr,
                ]),
                'numero' => $this->readFirstValue([
                    $tomaEndereco->nro,
                    $tomaEndereco->endNac->nro,
                ]),
                'complemento' => $this->readFirstValue([
                    $tomaEndereco->xCpl,
                    $tomaEndereco->endNac->xCpl,
                ]),
                'bairro' => $this->readFirstValue([
                    $tomaEndereco->xBairro,
                    $tomaEndereco->endNac->xBairro,
                ]),
                'municipio' => $this->readFirstValue([
                    $tomaEndereco->xMun,
                    $tomaEndereco->endNac->xMun,
                    $tomaEndereco->endNac->cMun,
                ]),
                'cep' => $this->formatCep((string) $tomaEndereco->endNac->CEP),
            ],
            'servico' => [
                'codTribNac' => (string) $dps->serv->cServ->cTribNac,
                'codTribMun' => (string) $dps->serv->cServ->cTribMun,
                'descricao' => (string) $dps->serv->cServ->xDescServ,
            ],
            'valores' => [
                'baseCalculo' => (float) $infNFSe->valores->vBC,
                'aliquotaAplicada' => (float) $infNFSe->valores->pAliqAplic,
                'valorIssqn' => (float) $infNFSe->valores->vISSQN,
                'valorServico' => (float) $dps->valores->vServPrest->vServ,
                'valorLiquido' => (float) $infNFSe->valores->vLiq,
                'valorTotalRet' => (float) $infNFSe->valores->vTotalRet,
            ],
            'dps' => [
                'numero' => (string) $dps->nDPS,
                'serie' => (string) $dps->serie,
                'competencia' => $this->formatDate((string) $dps->dCompet),
                'dataEmissao' => $this->formatDateTime((string) $dps->dhEmi),
            ],
            'tributacao' => [
                'tribISSQN' => (string) $dps->valores->trib->tribMun->tribISSQN,
                'tpRetISSQN' => (string) $dps->valores->trib->tribMun->tpRetISSQN,
                'totTribFed' => (float) $totaisTributos->vTotTribFed,
                'totTribEst' => (float) $totaisTributos->vTotTribEst,
                'totTribMun' => (float) $totaisTributos->vTotTribMun,
            ],
        ];

        return $this;
    }

    public function generate()
    {
        $this->pdf->AddPage();

        $this->addHeader();
        $this->addHorizontalLine();
        $this->addDadosNfse();
        $this->addHorizontalLine();
        $this->addEmitente();
        $this->addHorizontalLine();
        $this->addTomador();
        $this->addHorizontalLine(false);
        $this->addServico();
        $this->addHorizontalLine();
        $this->addTributacao();
        $this->addHorizontalLine();
        $this->addValores();
        $this->addHorizontalLine();
        $this->addTotaisTributos();

        // Draw border around the entire document after all content is added
        // This ensures it encompasses everything including "INFORMAÇÕES COMPLEMENTARES"
        $this->drawDocumentBorder();

        return $this->pdf;
    }

    private function drawDocumentBorder()
    {
        // Draw a rectangle border around the entire document
        // Using absolute coordinates from page top-left corner
        $pageWidth = 210; // A4 width in mm
        $pageHeight = 297; // A4 height in mm

        $x1 = $this->margin - 3;
        $y1 = $this->margin - 3;
        $width = $pageWidth - (2 * $this->margin - 5);  // Total width minus both margins
        $height = $pageHeight - (2 * $this->margin - 5); // Total height minus both margins

        // Set line width for border
        $this->pdf->SetLineWidth(0.1);

        // Draw rectangle border using absolute coordinates from page top
        // Rect(x, y, width, height, style)
        $this->pdf->Rect($x1, $y1, $width, $height, 'D');
    }

    private function addHorizontalLine($addLineHeight = true)
    {
        $y = $this->pdf->GetY();
        $pageWidth = 210; // A4 width in mm
        $rightEdge = $pageWidth - $this->margin;
        $this->pdf->Line($this->margin, $y, $rightEdge, $y);
        if ($addLineHeight) {
            $this->pdf->Ln(2);
        }
    }

    private function addHeader()
    {
        $startY = $this->pdf->GetY();

        // Left column - NFSe logo image (fixed base layout)
        $logoPath = __DIR__.'/../assets/logo-nfse-assinatura-horizontal.png';
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, $this->margin, $startY, 50, 0, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        // Center column - Main title (37.81% from HTML = ~80mm from page edge = ~70mm from margin)
        $centerX = 62; // Adjusted for 2mm margin (was 70, now 70-8=62)
        $this->pdf->SetXY($centerX, $startY);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(50, 4, 'DANFSe v1.0', 0, 0, 'C');
        $this->pdf->SetXY($centerX, $startY + 4);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(50, 4, 'Documento Auxiliar da NFS-e', 0, 0, 'C');

        // Right column - Municipality coat of arms (SVG) to the LEFT, text block to the RIGHT
        $rightX = 125; // base X for the right-side header block
        $blockWidth = 55;  // total width originally used for the right header cell
        $logoWidth = 18;
        $gap = 2;
        $textBlockWidth = $blockWidth - $logoWidth - $gap;

        // Municipality logo on the left of the text block
        $logoX = $rightX; // left edge of the right header block
        if (! empty($this->logoSvg)) {
            // TCPDF: '@' prefix means raw SVG content
            $this->pdf->ImageSVG('@'.$this->logoSvg, $logoX, $startY, $logoWidth, '', '', '', 0, false);
        } elseif ($defaultLogoPath = $this->resolveDefaultHeaderLogoPath()) {
            $this->pdf->Image($defaultLogoPath, $logoX, $startY + 2, $logoWidth, 0, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        // Text block to the RIGHT of the SVG logo
        $textX = $rightX + $logoWidth + $gap;
        $municipalityLine = $this->headerInfo['municipalityLine'] ?? ('Prefeitura Municipal de '.$this->data['localEmissao']);
        $secretariatLine = $this->headerInfo['secretariatLine'] ?? 'Secretaria Municipal da Fazenda';
        $phoneLine = $this->headerInfo['phoneLine'] ?? '';
        $emailLine = $this->headerInfo['emailLine'] ?? '';

        $this->pdf->SetXY($textX, $startY);
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->Cell($textBlockWidth, 3, $municipalityLine, 0, 1, 'L');
        $this->pdf->SetXY($textX, $startY + 3);
        $this->pdf->SetFont('helvetica', '', 6);
        $this->pdf->Cell($textBlockWidth, 2.5, $secretariatLine, 0, 1, 'L');
        $this->pdf->SetXY($textX, $startY + 5.5);
        $this->pdf->Cell($textBlockWidth, 2.5, $phoneLine, 0, 1, 'L');
        $this->pdf->SetXY($textX, $startY + 8);
        $this->pdf->Cell($textBlockWidth, 2.5, $emailLine, 0, 1, 'L');

        // Move Y position down for next content
        $this->pdf->SetY($startY + 12);
        $this->pdf->Ln(1);
    }

    private function addChaveAcesso()
    {
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'Chave de Acesso da NFS-e', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);
        // No border - just display the text
        $this->pdf->Cell(0, 4, $this->data['chaveAcesso'], 0, 1, 'L');
        $this->pdf->Ln(1);
    }

    private function addDadosNfse()
    {
        // Column positions matching the exact layout from screenshot
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $startY = $this->pdf->GetY();

        // Chave de Acesso row - spans all columns
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W + $col2W + $col3W + $col4W, 4, 'Chave de Acesso da NFS-e', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W + $col2W + $col3W + $col4W, 4, $this->data['chaveAcesso'], 0, 1, 'L');

        // First row - NFS-e headers
        $row1Y = $this->pdf->GetY();

        // QR Code positioned FIRST in 4th column (centered, larger, above all text)
        $qrUrl = 'https://www.nfse.gov.br/ConsultaPublica?tpc=1&chave='.$this->data['chaveAcesso'];
        $qrSize = 18; // QR code size
        // Center the QR code horizontally in the 4th column
        $qrX = $col4X + ($col4W - $qrSize) / 1.5;
        // Position QR code higher above row1Y to avoid overlapping with text
        $qrY = $row1Y - 10;

        $style = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1,
        ];

        $this->pdf->write2DBarcode($qrUrl, 'QRCODE,L', $qrX, $qrY, $qrSize, $qrSize, $style, 'N');

        // Now draw the text in columns 1-3
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row1Y);
        $this->pdf->Cell($col1W, 4, 'Número da NFS-e', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row1Y);
        $this->pdf->Cell($col2W, 4, 'Competência da NFS-e', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row1Y);
        $this->pdf->Cell($col3W, 4, 'Data e Hora da emissão da NFS-e', 0, 0, 'L');

        // Second row - NFS-e data
        $this->pdf->SetFont('helvetica', '', 8);
        $row2Y = $row1Y + 4;
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, $this->data['numeroNfse'], 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y);
        $this->pdf->Cell($col2W, 4, $this->data['dps']['competencia'], 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y);
        $this->pdf->Cell($col3W, 4, $this->data['dataProcessamento'], 0, 0, 'L');
        // Empty cell for row 2, column 4 (QR code occupies this space)
        $this->pdf->SetXY($col4X, $row2Y);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Third row - DPS headers (4th column empty)
        $row3Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row3Y);
        $this->pdf->Cell($col1W, 4, 'Número da DPS', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y);
        $this->pdf->Cell($col2W, 4, 'Série da DPS', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y);
        $this->pdf->Cell($col3W, 4, 'Data e Hora da emissão da DPS', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Fourth row - DPS data (authenticity message in 4th column, below QR code)
        $this->pdf->SetFont('helvetica', '', 8);
        $row4Y = $row3Y + 4;
        $this->pdf->SetXY($col1X, $row4Y);
        $this->pdf->Cell($col1W, 4, $this->data['dps']['numero'], 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row4Y);
        $this->pdf->Cell($col2W, 4, $this->data['dps']['serie'], 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row4Y);
        $this->pdf->Cell($col3W, 4, $this->data['dps']['dataEmissao'], 0, 0, 'L');

        // Authenticity message positioned in 4th column, below QR code
        $this->pdf->SetXY($col4X, $row4Y);
        $this->pdf->SetFont('helvetica', '', 5);
        $message = 'A autenticidade desta NFS-e pode ser verificada pela leitura deste código QR ou pela consulta da chave de acesso no portal nacional da NFS-e';
        $this->pdf->MultiCell($col4W - 1, 1, $message, 0, 'L', false, 1, $col4X + 5, $row4Y - 4);
        $messageEndY = $this->pdf->GetY();

        // Move Y position after QR code area (use the maximum of message end or QR code end)
        $this->pdf->SetY(max($row1Y + $qrSize, $messageEndY) + 2);
        $this->pdf->Ln(1);
    }

    private function addEmitente()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $emit = $this->data['emitente'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'EMITENTE DA NFS-e', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'CNPJ / CPF / NIF', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Inscrição Municipal', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY);
        $this->pdf->Cell($col4W, 4, 'Telefone', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, 'Prestador do Serviço', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, $emit['cnpj'], 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, $emit['im'] ?: '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY + 4);
        $this->pdf->Cell($col4W, 4, $emit['fone'], 0, 1, 'L');

        // Header row
        $row2Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, 'Nome / Nome Empresarial', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y);
        $this->pdf->Cell($col3W, 4, 'E-mail', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row2Y + 4);
        $this->pdf->Cell($col1W, 4, $emit['nome'], 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y + 4);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y + 4);
        $this->pdf->Cell($col3W, 4, $emit['email'], 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y + 4);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        $endereco = $emit['logradouro'].', '.$emit['numero'].', '.$emit['bairro'];
        // Header row
        $row3Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row3Y);
        $this->pdf->Cell($col1W, 4, 'Endereço', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y);
        $this->pdf->Cell($col3W, 4, 'Município', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y);
        $this->pdf->Cell($col4W, 4, 'CEP', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row3Y + 4);
        $this->pdf->Cell($col1W, 4, $endereco, 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y + 4);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y + 4);
        $this->pdf->Cell($col3W, 4, $this->data['localEmissao'].' - '.$emit['uf'], 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y + 4);
        $this->pdf->Cell($col4W, 4, $emit['cep'], 0, 1, 'L');

        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $this->pdf->GetY());
        $this->pdf->Cell($col1W, 4, 'Simples Nacional na Data de Competência', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $this->pdf->GetY());
        $this->pdf->Cell($col3W + $col4W, 4, 'Regime de Apuração Tributária pelo SN', 0, 1, 'L');
        $this->pdf->SetXY($col1X, $this->pdf->GetY());
        $this->pdf->Cell($col1W, 4, 'Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP)', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $this->pdf->GetY());
        $this->pdf->Cell($col3W + $col4W, 4, 'Regime de apuração dos tributos federais e municipal pelo Simples Nacional', 0, 1, 'L');
        $this->pdf->Ln(1);
    }

    private function addTomador()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $toma = $this->data['tomador'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'TOMADOR DO SERVIÇO', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'CNPJ / CPF / NIF', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Inscrição Municipal', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY);
        $this->pdf->Cell($col4W, 4, 'Telefone', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, $toma['cnpj'], 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY + 4);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Header row
        $row2Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, 'Nome / Nome Empresarial', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y);
        $this->pdf->Cell($col3W, 4, 'E-mail', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Data row with dynamic height to avoid overlap on long company names
        $this->pdf->SetFont('helvetica', '', 8);
        $lineHeight = 4;
        $row2DataY = $row2Y + 4;
        $nameWidth = $col1W + $col2W;
        $emailTomador = '-';
        $row2Height = $this->calculateWrappedRowHeight([
            ['text' => $toma['nome'], 'width' => $nameWidth],
            ['text' => $emailTomador, 'width' => $col3W],
        ], $lineHeight);

        $this->drawWrappedCell($col1X, $row2DataY, $nameWidth, $lineHeight, $row2Height, $toma['nome']);
        $this->drawWrappedCell($col3X, $row2DataY, $col3W, $lineHeight, $row2Height, $emailTomador);

        $this->pdf->SetY($row2DataY + $row2Height);

        $endereco = $this->buildAddressLine($toma);
        $municipioTomador = $this->displayValue($toma['municipio'] ?? '', $this->data['localIncidencia'] ?? '-');
        $cepTomador = $this->displayValue($toma['cep'] ?? '');

        // Header row
        $row3Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row3Y);
        $this->pdf->Cell($col1W, 4, 'Endereço', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y);
        $this->pdf->Cell($col2W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y);
        $this->pdf->Cell($col3W, 4, 'Município', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y);
        $this->pdf->Cell($col4W, 4, 'CEP', 0, 1, 'L');

        // Data row with dynamic height to avoid overlap on long addresses
        $this->pdf->SetFont('helvetica', '', 8);
        $rowDataY = $row3Y + 4;
        $addressWidth = $col1W + $col2W;
        $rowHeight = $this->calculateWrappedRowHeight([
            ['text' => $endereco, 'width' => $addressWidth],
            ['text' => $municipioTomador, 'width' => $col3W],
            ['text' => $cepTomador, 'width' => $col4W],
        ], $lineHeight);

        $this->drawWrappedCell($col1X, $rowDataY, $addressWidth, $lineHeight, $rowHeight, $endereco);
        $this->drawWrappedCell($col3X, $rowDataY, $col3W, $lineHeight, $rowHeight, $municipioTomador);
        $this->drawWrappedCell($col4X, $rowDataY, $col4W, $lineHeight, $rowHeight, $cepTomador);

        $this->pdf->SetY($rowDataY + $rowHeight);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', '', 7);
        $this->addHorizontalLine(false);
        $intermediarioMessage = 'INTERMEDIÁRIO DO SERVIÇO NÃO IDENTIFICADO NA NFS-e';
        $intermediarioWidth = 210 - ($this->margin * 2);
        $intermediarioY = $this->pdf->GetY();
        $intermediarioHeight = $this->calculateWrappedRowHeight([
            ['text' => $intermediarioMessage, 'width' => $intermediarioWidth],
        ], 4);
        $this->pdf->SetXY($this->margin, $intermediarioY);
        $this->pdf->MultiCell($intermediarioWidth, 4, $intermediarioMessage, 0, 'C', false, 1, $this->margin, $intermediarioY, true, 0, false, true, $intermediarioHeight, 'M', false);
    }

    private function addServico()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'SERVIÇO PRESTADO', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);

        $serv = $this->data['servico'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'Código de Tributação Nacional', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'Código de Tributação Municipal', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Local da Prestação', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY);
        $this->pdf->Cell($col4W, 4, 'País da Prestação', 0, 1, 'L');

        // Data row - Format code as 01.03.02
        $this->pdf->SetFont('helvetica', '', 8);
        $codTribFormatted = $this->formatCodTribNac($serv['codTribNac']);
        $codTrib = $codTribFormatted.' - '.substr($this->data['tribNac'], 0, 40).'...';
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, $codTrib, 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, $serv['codTribMun'] ?: '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, $this->data['localPrestacao'], 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY + 4);
        $this->pdf->Cell($col4W, 4, '-', 0, 1, 'L');

        // Descrição com altura dinâmica para evitar overflow
        $row2Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, 'Descrição do Serviço', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 8);
        $descricaoWidth = $col2W + $col3W + $col4W;
        $descricaoHeight = $this->calculateWrappedRowHeight([
            ['text' => $serv['descricao'], 'width' => $descricaoWidth],
        ], 4);
        $this->drawWrappedCell($col2X, $row2Y, $descricaoWidth, 4, $descricaoHeight, $serv['descricao']);
        $this->pdf->SetY($row2Y + $descricaoHeight);
        $this->pdf->Ln(2);
    }

    private function addTributacao()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'TRIBUTAÇÃO MUNICIPAL', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);

        $trib = $this->data['tributacao'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'Tributação do ISSQN', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'País Resultado da Prestação do Serviço', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Município de Incidência do ISSQN', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY);
        $this->pdf->Cell($col4W, 4, 'Regime Especial de Tributação', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, 'Operação Tributável', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, $this->data['localIncidencia'], 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY + 4);
        $this->pdf->Cell($col4W, 4, 'Nenhum', 0, 1, 'L');

        // Header row
        $row2Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, 'Tipo de Imunidade', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y);
        $this->pdf->Cell($col2W, 4, 'Suspensão da Exigibilidade do ISSQN', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y);
        $this->pdf->Cell($col3W, 4, 'Número Processo Suspensão', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y);
        $this->pdf->Cell($col4W, 4, 'Benefício Municipal', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row2Y + 4);
        $this->pdf->Cell($col1W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y + 4);
        $this->pdf->Cell($col2W, 4, 'Não', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y + 4);
        $this->pdf->Cell($col4W, 4, '-', 0, 1, 'L');

        $val = $this->data['valores'];
        $trib = $this->data['tributacao'];
        // Header row
        $row3Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row3Y);
        $this->pdf->Cell($col1W, 4, 'Valor do Serviço', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y);
        $this->pdf->Cell($col2W, 4, 'Desconto Incondicionado', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y);
        $this->pdf->Cell($col3W, 4, 'Total Deduções/Reduções', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y);
        $this->pdf->Cell($col4W, 4, 'Cálculo do BM', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row3Y + 4);
        $this->pdf->Cell($col1W, 4, 'R$ '.number_format($val['valorServico'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row3Y + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row3Y + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row3Y + 4);
        $this->pdf->Cell($col4W, 4, '-', 0, 1, 'L');

        // Header row
        $row4Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row4Y);
        $this->pdf->Cell($col1W, 4, 'BC ISSQN', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row4Y);
        $this->pdf->Cell($col2W, 4, 'Alíquota Aplicada', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row4Y);
        $this->pdf->Cell($col3W, 4, 'Retenção do ISSQN', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row4Y);
        $this->pdf->Cell($col4W, 4, 'ISSQN Apurado', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row4Y + 4);
        $this->pdf->Cell($col1W, 4, 'R$ '.number_format($val['baseCalculo'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row4Y + 4);
        $this->pdf->Cell($col2W, 4, number_format($val['aliquotaAplicada'], 2, ',', '.').' %', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row4Y + 4);
        $this->pdf->Cell($col3W, 4, $this->formatRetencaoIssqn($trib['tpRetISSQN']), 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row4Y + 4);
        $this->pdf->Cell($col4W, 4, 'R$ '.number_format($val['valorIssqn'], 2, ',', '.'), 0, 1, 'L');

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'TRIBUTAÇÃO FEDERAL', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);

        // Header row
        $row5Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row5Y);
        $this->pdf->Cell($col1W, 4, 'IRRF', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row5Y);
        $this->pdf->Cell($col2W, 4, 'CP', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row5Y);
        $this->pdf->Cell($col3W, 4, 'CSLL', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row5Y);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row5Y + 4);
        $this->pdf->Cell($col1W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row5Y + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row5Y + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row5Y + 4);
        $this->pdf->Cell($col4W, 4, '', 0, 1, 'L');

        // Header row
        $row6Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row6Y);
        $this->pdf->Cell($col1W, 4, 'PIS', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row6Y);
        $this->pdf->Cell($col2W, 4, 'COFINS', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row6Y);
        $this->pdf->Cell($col3W, 4, 'Retenção do PIS/COFINS', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row6Y);
        $this->pdf->Cell($col4W, 4, 'TOTAL TRIBUTAÇÃO FEDERAL', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row6Y + 4);
        $this->pdf->Cell($col1W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row6Y + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row6Y + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row6Y + 4);
        $this->pdf->Cell($col4W, 4, '-', 0, 1, 'L');
        $this->pdf->Ln(2);
    }

    private function addValores()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 47; // Adjusted for 2mm margin (was 55, now 55-8=47)
        $col3X = 97; // Adjusted for 2mm margin (was 105, now 105-8=97)
        $col4X = 147; // Adjusted for 2mm margin (was 155, now 155-8=147)
        $col1W = 45;
        $col2W = 50;
        $col3W = 50;
        $col4W = 45;

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'VALOR TOTAL DA NFS-E', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);

        $val = $this->data['valores'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'Valor do Serviço', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'Desconto Condicionado', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Desconto Incondicionado', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY);
        $this->pdf->Cell($col4W, 4, 'ISSQN Retido', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, 'R$ '.number_format($val['valorServico'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $startY + 4);
        $this->pdf->Cell($col4W, 4, '-', 0, 1, 'L');

        // Header row
        $row2Y = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $row2Y);
        $this->pdf->Cell($col1W, 4, 'IRRF, CP,CSLL - Retidos', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y);
        $this->pdf->Cell($col2W, 4, 'PIS/COFINS Retidos', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y);
        $this->pdf->Cell($col3W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y);
        $this->pdf->Cell($col4W, 4, 'Valor Líquido da NFS-e', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $row2Y + 4);
        $this->pdf->Cell($col1W, 4, 'R$ '.number_format($val['valorTotalRet'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col2X, $row2Y + 4);
        $this->pdf->Cell($col2W, 4, '-', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $row2Y + 4);
        $this->pdf->Cell($col3W, 4, '', 0, 0, 'L');
        $this->pdf->SetXY($col4X, $row2Y + 4);
        $this->pdf->Cell($col4W, 4, 'R$ '.number_format($val['valorLiquido'], 2, ',', '.'), 0, 1, 'L');
        $this->pdf->Ln(2);
    }

    private function addTotaisTributos()
    {
        $col1X = $this->margin; // Adjusted for 2mm margin (was 10)
        $col2X = 62; // Adjusted for 2mm margin (was 70, now 70-8=62)
        $col3X = 122; // Adjusted for 2mm margin (was 130, now 130-8=122)
        $col1W = 60;
        $col2W = 60;
        $col3W = 60;

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'TOTAIS APROXIMADOS DOS TRIBUTOS', 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 8);

        $trib = $this->data['tributacao'];
        $startY = $this->pdf->GetY();

        // Header row
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetXY($col1X, $startY);
        $this->pdf->Cell($col1W, 4, 'Federais', 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY);
        $this->pdf->Cell($col2W, 4, 'Estaduais', 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY);
        $this->pdf->Cell($col3W, 4, 'Municípios', 0, 1, 'L');

        // Data row
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY($col1X, $startY + 4);
        $this->pdf->Cell($col1W, 4, 'R$ '.number_format($trib['totTribFed'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col2X, $startY + 4);
        $this->pdf->Cell($col2W, 4, 'R$ '.number_format($trib['totTribEst'], 2, ',', '.'), 0, 0, 'L');
        $this->pdf->SetXY($col3X, $startY + 4);
        $this->pdf->Cell($col3W, 4, 'R$ '.number_format($trib['totTribMun'], 2, ',', '.'), 0, 1, 'L');
        $this->pdf->Ln(5);

        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->Cell(0, 4, 'INFORMAÇÕES COMPLEMENTARES', 0, 1, 'L');
    }

    private function addTableRowWithBorders($headers, $data, $widths)
    {
        $this->pdf->SetFont('helvetica', 'B', 8);
        for ($i = 0; $i < count($headers); $i++) {
            $this->pdf->Cell($widths[$i], 5, $headers[$i], 0, 0, 'L');
        }
        $this->pdf->Ln();

        $this->pdf->SetFont('helvetica', '', 8);
        for ($i = 0; $i < count($data); $i++) {
            $this->pdf->Cell($widths[$i], 5, $data[$i], 0, 0, 'L');
        }
        $this->pdf->Ln();
    }

    private function formatCnpjCpf($value)
    {
        $value = preg_replace('/\D/', '', $value);
        if (strlen($value) == 14) {
            return substr($value, 0, 2).'.'.substr($value, 2, 3).'.'.substr($value, 5, 3).'/'.substr($value, 8, 4).'-'.substr($value, 12, 2);
        } elseif (strlen($value) == 11) {
            return substr($value, 0, 3).'.'.substr($value, 3, 3).'.'.substr($value, 6, 3).'-'.substr($value, 9, 2);
        }

        return $value;
    }

    private function formatCep($value)
    {
        $value = preg_replace('/\D/', '', $value);
        if (strlen($value) == 8) {
            return substr($value, 0, 5).'-'.substr($value, 5, 3);
        }

        return $value;
    }

    private function formatPhone($value)
    {
        $value = preg_replace('/\D/', '', $value);
        if (strlen($value) == 11) {
            return '('.substr($value, 0, 2).') '.substr($value, 2, 5).'-'.substr($value, 7, 4);
        } elseif (strlen($value) == 10) {
            return '('.substr($value, 0, 2).') '.substr($value, 2, 4).'-'.substr($value, 6, 4);
        }

        return $value;
    }

    private function formatDate($value)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $matches)) {
            return $matches[3].'/'.$matches[2].'/'.$matches[1];
        }

        return $value;
    }

    private function formatDateTime($value)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/', $value, $matches)) {
            return $matches[3].'/'.$matches[2].'/'.$matches[1].' '.$matches[4].':'.$matches[5].':'.$matches[6];
        }

        return $value;
    }

    private function formatCodTribNac($value)
    {
        $value = preg_replace('/\D/', '', $value);
        if (strlen($value) == 6) {
            return substr($value, 0, 2).'.'.substr($value, 2, 2).'.'.substr($value, 4, 2);
        }

        return $value;
    }

    private function readFirstValue(array $values)
    {
        foreach ($values as $value) {
            $stringValue = trim((string) $value);
            if ($stringValue !== '') {
                return $stringValue;
            }
        }

        return '';
    }

    private function formatRetencaoIssqn($value)
    {
        return match ((string) $value) {
            '1' => 'Não Retido',
            '2' => 'Retido',
            default => (string) $value ?: '-',
        };
    }

    private function buildAddressLine(array $party): string
    {
        $parts = [
            trim((string) ($party['logradouro'] ?? '')),
            trim((string) ($party['numero'] ?? '')),
            trim((string) ($party['complemento'] ?? '')),
            trim((string) ($party['bairro'] ?? '')),
        ];

        return implode(', ', array_values(array_filter($parts, fn ($part) => $part !== '')));
    }

    private function displayValue($value, string $fallback = '-'): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    private function calculateWrappedRowHeight(array $columns, float $lineHeight): float
    {
        $maxLines = 1;

        foreach ($columns as $column) {
            $text = $this->displayValue($column['text'] ?? '');
            $width = (float) ($column['width'] ?? 0);
            $lines = max(1, (int) $this->pdf->getNumLines($text, $width));
            $maxLines = max($maxLines, $lines);
        }

        return $maxLines * $lineHeight;
    }

    private function drawWrappedCell(float $x, float $y, float $width, float $lineHeight, float $rowHeight, string $text): void
    {
        $this->pdf->SetXY($x, $y);
        $this->pdf->MultiCell(
            $width,
            $lineHeight,
            $this->displayValue($text),
            0,
            'L',
            false,
            0,
            $x,
            $y,
            true,
            0,
            false,
            true,
            $rowHeight,
            'T',
            false
        );
    }

    private function resolveDefaultHeaderLogoPath(?string $path = null): ?string
    {
        $path ??= public_path('images/application/nfse.png');

        return is_file($path) ? $path : null;
    }

    private function loadXml($xmlInput)
    {
        if ($xmlInput instanceof \SimpleXMLElement) {
            return $xmlInput;
        }

        $xmlInput = trim((string) $xmlInput);
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            if ($this->looksLikeXmlContent($xmlInput)) {
                $xml = simplexml_load_string($xmlInput);
            } else {
                $xml = simplexml_load_file($xmlInput);
            }

            if ($xml === false) {
                $message = 'Failed to parse XML';
                $errors = libxml_get_errors();
                if (! empty($errors)) {
                    $message .= ': '.trim($errors[0]->message);
                }

                throw new \Exception($message);
            }

            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
    }

    private function looksLikeXmlContent(string $value): bool
    {
        return str_starts_with($value, '<?xml') || str_starts_with($value, '<');
    }
}
