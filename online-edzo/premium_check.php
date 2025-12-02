<?php
function isPremium($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT plan_type, status, end_date 
        FROM subscriptions 
        WHERE user_id = ? 
        AND status = 'active' 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $sub = $stmt->fetch();
    
    if (!$sub) {
        return false;
    }
    
    // Ha trial vagy premium és még nem járt le
    if (($sub['plan_type'] === 'premium' || $sub['plan_type'] === 'trial') 
        && ($sub['end_date'] === null || $sub['end_date'] >= date('Y-m-d'))) {
        return true;
    }
    
    return false;
}

function getUserPlan($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT plan_type, status, start_date, end_date 
        FROM subscriptions 
        WHERE user_id = ? 
        AND status = 'active' 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
?>