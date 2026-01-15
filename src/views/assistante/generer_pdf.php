<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoloader
require_once __DIR__ . '/../../../vendor/autoload.php';

// session_start(); // Assuming session is already started in a common header/config
// require_once '../../config/session_check.php'; // Assuming session check is handled
// require_once '../../src/models/Soutenance.php'; // Assuming a Soutenance model exists

// Placeholder for fetching real data for the PDF
// For example, get a soutenance ID from a query parameter
// $soutenanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// if ($soutenanceId === 0) {
//     die('No soutenance ID provided.');
// }
// $soutenance = fetchSoutenanceDetails($soutenanceId); // Placeholder function

// --- Start PDF Generation ---
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
        // Assuming logo is in public/assets/img/
        $this->Image(__DIR__ . '/../../../public/assets/img/logo_uemf.png', 10, 6, 30);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Convocation', 1, 0, 'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Instantiation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times', '', 12);

// Placeholder PDF content
// In a real application, you would populate this with data from $soutenance
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Convocation a la Soutenance de PFE', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 10, 'Etudiant: John Doe', 0, 1);
$pdf->Cell(0, 10, 'Sujet: Developpement d\'une application web', 0, 1);
$pdf->Cell(0, 10, 'Encadrant: Prof. Smith', 0, 1);
$pdf->Ln(5);

$pdf->Cell(0, 10, 'Date: 2026-02-15', 0, 1);
$pdf->Cell(0, 10, 'Heure: 10:00', 0, 1);
$pdf->Cell(0, 10, 'Salle: A101', 0, 1);
$pdf->Ln(10);

$pdf->MultiCell(0, 10, 'Vous etes convoque(e) a la soutenance de votre Projet de Fin d\'Etudes. Veuillez vous presenter 15 minutes avant l\'heure indiquee, muni(e) de votre carte d\'etudiant.');
$pdf->Ln(20);

$pdf->Cell(0, 10, 'Le Jury:', 0, 1);
$pdf->Cell(0, 10, 'President: Prof. Johnson', 0, 1);
$pdf->Cell(0, 10, 'Examinateur: Prof. Williams', 0, 1);
$pdf->Ln(20);

$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(0, 10, 'Fait a Fes, le ' . date('d/m/Y'), 0, 1, 'R');

$filename = 'convocation_' . time() . '.pdf';
$filepath = __DIR__ . '/../../../public/archives/' . $filename;
$pdf->Output('F', $filepath);

echo "PDF successfully generated and archived at: <a href='/Gestion-Soutenances/public/archives/$filename'>$filename</a>";
?>
