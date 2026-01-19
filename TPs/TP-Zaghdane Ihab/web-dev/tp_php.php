<?php
$etudiants = [
    [
        "id" => 1,
        "nom" => "Zaghdane",
        "prenom" => "Ihab",
        "notes" => ["Web" => 15, "Reseau" => 14, "Linux" => 18]
    ],
    [
        "id" => 2,
        "nom" => "Bernard",
        "prenom" => "Luc",
        "notes" => ["Web" => 8, "Reseau" => 7, "Linux" => 9]
    ],
    [
        "id" => 3,
        "nom" => "Vinicius",
        "prenom" => "Junior",
        "notes" => ["Web" => 10, "Reseau" => 10, "Linux" => 11]
    ],
    [
        "id" => 4,
        "nom" => "Petit",
        "prenom" => "Chloé",
        "notes" => ["Math" => 19, "Physique" => 17, "Linux" => 20]
    ]
];

function getStatus($moyenne) {
    return ($moyenne >= 10) ? "Validé " : "Non Validé ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notes Etudiants</title>
</head>
<body>

    <h2>Resultats</h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom Complet</th>
                <th>Moyenne</th>
                <th>Verdict</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants as $eleve): ?>
                <?php 
                    $somme = array_sum($eleve['notes']);
                    $nbModules = count($eleve['notes']);
                    $moyenne = $somme / $nbModules;
                    
                    $status = getStatus($moyenne);
                ?>
                <tr>
                    <td><?php echo $eleve['id']; ?></td>
                    <td><?php echo $eleve['prenom'] . " " . $eleve['nom']; ?></td>
                    <td><?php echo number_format($moyenne, 2); ?></td>
                    <td><?php echo $status; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>