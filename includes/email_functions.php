<?php
function sendEmail($to, $subject, $content) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Mon Épargne <no-reply@mon-epargne.com>' . "\r\n";
    
    return mail($to, $subject, $content, $headers);
}

function sendGoalProgressEmail($email, $goal_name, $progress, $target_amount, $saved_amount) {
    $subject = "";
    $content = "";
    
    if ($progress >= 100) {
        $subject = "🎉 Félicitations ! Objectif {$goal_name} atteint !";
        $content = "<h2>Félicitations !</h2>";
        $content .= "<p>Vous avez atteint 100% de votre objectif <strong>{$goal_name}</strong> !</p>";
    } else {
        $subject = "👍 Vous êtes à {$progress}% de votre objectif {$goal_name}";
        $content = "<h2>Bon travail !</h2>";
        $content .= "<p>Vous avez atteint {$progress}% de votre objectif <strong>{$goal_name}</strong> !</p>";
    }
    
    $content .= "<p><strong>Détails :</strong></p>";
    $content .= "<ul>";
    $content .= "<li>Objectif: ".formatMontant($target_amount)."</li>";
    $content .= "<li>Épargné: ".formatMontant($saved_amount)."</li>";
    
    if ($progress < 100) {
        $remaining = $target_amount - $saved_amount;
        $content .= "<li>Reste à épargner: ".formatMontant($remaining)."</li>";
    }
    
    $content .= "</ul>";
    $content .= "<p>Connectez-vous pour voir vos progrès : <a href='[URL_DE_VOTRE_SITE]'>[URL_DE_VOTRE_SITE]</a></p>";
    
    return sendEmail($email, $subject, $content);
}

function prepareInactivityEmail($email, $days_inactive) {
    $subject = "Pensez à épargner !";
    $content = "<h2>Bonjour,</h2>";
    $content .= "<p>Cela fait {$days_inactive} jours que vous n'avez pas effectué de dépôt sur votre compte d'épargne.</p>";
    $content .= "<p>N'oubliez pas que l'épargne régulière est la clé pour atteindre vos objectifs financiers !</p>";
    $content .= "<p>Connectez-vous dès maintenant pour ajouter un nouveau dépôt : <a href='[URL_DE_VOTRE_SITE]'>[URL_DE_VOTRE_SITE]</a></p>";
    
    return sendEmail($email, $subject, $content);
}