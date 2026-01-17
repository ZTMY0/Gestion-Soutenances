<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoloader
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';

<<<<<<< Updated upstream
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
=======
 session_start(); // Assuming session is already started in a common header/config
 require_once '../../../config/session_check.php'; // Assuming session check is handled
 require_once '../../../config/database.php'; // Include database connection
 require_once '../../../src/models/Soutenance.php'; // Include Soutenance model
 
 use App\Models\Soutenance; // Use the namespace

// New helper function for encoding
function utf8_to_iso($text) {
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}
 
 // Get soutenance ID from query parameter
 $soutenanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
 
 if ($soutenanceId === 0) {
     die('Erreur: Aucun ID de soutenance fourni.');
 }
 
 // Fetch soutenance details
 $soutenance = Soutenance::getDetails($pdo, $soutenanceId);
 
 if (!$soutenance) {
     die('Erreur: Soutenance non trouvée ou erreur de base de données.');
 }
 
 // --- Start PDF Generation ---
>>>>>>> Stashed changes
class PDF extends FPDF
{
    // Page header
    function MyCustomHeader()
    {
        // Logo
        // Assuming logo is in public/assets/img/
        $this->Image(__DIR__ . '/../../../public/assets/img/logo_uemf.png', 10, 6, 30);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, utf8_to_iso('Convocation'), 1, 0, 'C');
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
        $this->Cell(0, 10, utf8_to_iso('Page ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
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
$pdf->Cell(0, 10, utf8_to_iso('Convocation à la Soutenance de PFE'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 10, utf8_to_iso('Étudiant: ') . utf8_to_iso(htmlspecialchars($soutenance['etudiant_prenom'] . ' ' . $soutenance['etudiant_nom'])), 0, 1);
if (!empty($soutenance['binome_nom'])) {
    $pdf->Cell(0, 10, utf8_to_iso('Binôme: ') . utf8_to_iso(htmlspecialchars($soutenance['binome_prenom'] . ' ' . $soutenance['binome_nom'])), 0, 1);
}
$pdf->Cell(0, 10, utf8_to_iso('Sujet: ') . utf8_to_iso(htmlspecialchars($soutenance['projet_titre'])), 0, 1);
$pdf->Cell(0, 10, utf8_to_iso('Encadrant: ') . utf8_to_iso(htmlspecialchars($soutenance['encadrant_prenom'] . ' ' . $soutenance['encadrant_nom'])), 0, 1);
$pdf->Ln(5);

// Format date and time
$dateSoutenance = new DateTime($soutenance['date_soutenance']);
$pdf->Cell(0, 10, utf8_to_iso('Date: ') . $dateSoutenance->format('d/m/Y'), 0, 1);
$pdf->Cell(0, 10, utf8_to_iso('Heure: ') . $dateSoutenance->format('H:i'), 0, 1);
$pdf->Cell(0, 10, utf8_to_iso('Salle: ') . utf8_to_iso(htmlspecialchars($soutenance['salle_nom'])), 0, 1);
$pdf->Ln(10);

$pdf->MultiCell(0, 10, utf8_to_iso('Vous êtes convoqué(e) à la soutenance de votre Projet de Fin d\'Etudes. Veuillez vous présenter 15 minutes avant l\'heure indiquée, muni(e) de votre carte d\'étudiant.'));
$pdf->Ln(20);

if (!empty($soutenance['jury_members'])) {
    $pdf->Cell(0, 10, utf8_to_iso('Le Jury:'), 0, 1);
    foreach ($soutenance['jury_members'] as $member) {
        $pdf->Cell(0, 10, utf8_to_iso('- ' . htmlspecialchars($member)), 0, 1);
    }
}
$pdf->Ln(20);

$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(0, 10, utf8_to_iso('Fait à Fès, le ') . date('d/m/Y'), 0, 1, 'R');

$output_filename = 'convocation_' . str_replace(' ', '_', $soutenance['etudiant_nom'] . '_' . $soutenance['etudiant_prenom']) . '.pdf';

// Output the PDF for direct download
$pdf->Output('D', $output_filename);
?>
