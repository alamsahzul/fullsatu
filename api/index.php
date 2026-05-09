<?php
/**
 * FullSatu REST API Router
 * 
 * Simple routing for mobile app consumption.
 * All responses are JSON.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

// Parse the request
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /api/index.php or /fullsatu/api/index.php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // e.g. /api/seasons

$baseDir = dirname($scriptName); // e.g. /api
if ($baseDir === '/' || $baseDir === '\\') $baseDir = '';

$path = substr($requestUri, strlen($baseDir));
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];
$method = $_SERVER['REQUEST_METHOD'];

// Helper: JSON response
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper: Get JSON body
function getJsonBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Helper: Require admin auth
function requireAuth() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $token);
    
    if (!$token || !validateApiToken($token)) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function validateApiToken($token) {
    // Simple token validation - matches hash of admin credentials
    $expected = hash('sha256', ADMIN_USER . ':' . ADMIN_PASS . ':fullsatu_api_key');
    return hash_equals($expected, $token);
}

function generateApiToken() {
    return hash('sha256', ADMIN_USER . ':' . ADMIN_PASS . ':fullsatu_api_key');
}

// ============================================================
// ROUTING
// ============================================================

try {
    // --- AUTH ---
    if ($segments[0] === 'auth' && $method === 'POST') {
        $body = getJsonBody();
        $user = $body['username'] ?? '';
        $pass = $body['password'] ?? '';
        
        if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
            jsonResponse([
                'success' => true,
                'token' => generateApiToken(),
                'user' => $user
            ]);
        } else {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }

    // --- PLAYERS ---
    elseif ($segments[0] === 'players') {
        // GET /players - List all
        if ($method === 'GET' && count($segments) === 1) {
            $stmt = $pdo->query("SELECT * FROM players ORDER BY name ASC");
            jsonResponse(['players' => $stmt->fetchAll()]);
        }
        // GET /players/{id} - Detail with stats
        elseif ($method === 'GET' && count($segments) === 2) {
            $id = (int)$segments[1];
            $stmt = $pdo->prepare("SELECT * FROM players WHERE id = ?");
            $stmt->execute([$id]);
            $player = $stmt->fetch();
            if (!$player) jsonResponse(['error' => 'Player not found'], 404);
            
            // Get global stats
            $stmtStats = $pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM matches m WHERE (m.player1_id = ? OR m.player2_id = ? OR m.p1_partner_id = ? OR m.p2_partner_id = ?) AND m.status = 'completed') as total_matches,
                    (SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player1_id AND (m.player1_id = ? OR m.p1_partner_id = ?)) OR (m.winner_id = m.player2_id AND (m.player2_id = ? OR m.p2_partner_id = ?))) AND m.status = 'completed') as wins,
                    (SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player2_id AND (m.player1_id = ? OR m.p1_partner_id = ?)) OR (m.winner_id = m.player1_id AND (m.player2_id = ? OR m.p2_partner_id = ?))) AND m.status = 'completed') as losses
            ");
            $stmtStats->execute([$id,$id,$id,$id, $id,$id,$id,$id, $id,$id,$id,$id]);
            $stats = $stmtStats->fetch();
            $stats['points'] = ($stats['wins'] * 2) - $stats['losses'];
            $stats['win_rate'] = $stats['total_matches'] > 0 ? round(($stats['wins'] / $stats['total_matches']) * 100) : 0;
            
            $player['stats'] = $stats;
            
            // Get match history
            $stmtH = $pdo->prepare("
                SELECT m.*, s.name AS season_name, s.category AS season_category,
                    p1.name AS p1_name, p2.name AS p2_name,
                    pa1.name AS p1_partner, pa2.name AS p2_partner
                FROM matches m
                JOIN seasons s ON m.season_id = s.id
                JOIN players p1 ON m.player1_id = p1.id
                JOIN players p2 ON m.player2_id = p2.id
                LEFT JOIN players pa1 ON m.p1_partner_id = pa1.id
                LEFT JOIN players pa2 ON m.p2_partner_id = pa2.id
                WHERE (m.player1_id = ? OR m.player2_id = ? OR m.p1_partner_id = ? OR m.p2_partner_id = ?)
                AND m.status = 'completed'
                ORDER BY m.created_at DESC
            ");
            $stmtH->execute([$id, $id, $id, $id]);
            $player['match_history'] = $stmtH->fetchAll();
            
            jsonResponse(['player' => $player]);
        }
        // POST /players - Create (admin)
        elseif ($method === 'POST' && count($segments) === 1) {
            requireAuth();
            $body = getJsonBody();
            $name = trim($body['name'] ?? '');
            if (!$name) jsonResponse(['error' => 'Name is required'], 400);
            
            $stmt = $pdo->prepare("INSERT INTO players (name) VALUES (?)");
            $stmt->execute([$name]);
            jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()], 201);
        }
    }

    // --- SEASONS ---
    elseif ($segments[0] === 'seasons') {
        // GET /seasons
        if ($method === 'GET' && count($segments) === 1) {
            get_seasons($pdo);
        }
        // POST /seasons
        elseif ($method === 'POST' && count($segments) === 1) {
            create_season($pdo);
        }
        // GET /seasons/{id}/standings
        elseif ($method === 'GET' && count($segments) === 3 && $segments[2] === 'standings') {
            $seasonId = (int)$segments[1];
            $standings = calculateStandings($pdo, $seasonId);
            jsonResponse(['standings' => $standings]);
        }
        // GET /seasons/{id}/matches
        elseif ($method === 'GET' && count($segments) === 3 && $segments[2] === 'matches') {
            $seasonId = (int)$segments[1];
            $status = $_GET['status'] ?? null;
            
            $sql = "SELECT m.*, p1.name AS p1_name, p2.name AS p2_name, p1.photo AS p1_photo, p2.photo AS p2_photo,
                    pa1.name AS p1_partner, pa2.name AS p2_partner
                    FROM matches m
                    JOIN players p1 ON m.player1_id = p1.id
                    JOIN players p2 ON m.player2_id = p2.id
                    LEFT JOIN players pa1 ON m.p1_partner_id = pa1.id
                    LEFT JOIN players pa2 ON m.p2_partner_id = pa2.id
                    WHERE m.season_id = ?";
            $params = [$seasonId];
            
            if ($status) {
                $sql .= " AND m.status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY m.round_number, m.leg_number, m.id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['matches' => $stmt->fetchAll()]);
        }
    }

    // --- MATCHES ---
    elseif ($segments[0] === 'matches') {
        // GET /matches/{id}
        if ($method === 'GET' && count($segments) === 2) {
            $id = (int)$segments[1];
            $stmt = $pdo->prepare("
                SELECT m.*, s.name AS season_name, s.format, s.category,
                    p1.name AS p1_name, p1.photo AS p1_photo,
                    pa1.name AS p1_partner, pa1.photo AS p1_partner_photo,
                    p2.name AS p2_name, p2.photo AS p2_photo,
                    pa2.name AS p2_partner, pa2.photo AS p2_partner_photo
                FROM matches m
                JOIN seasons s ON m.season_id = s.id
                JOIN players p1 ON m.player1_id = p1.id
                LEFT JOIN players pa1 ON m.p1_partner_id = pa1.id
                JOIN players p2 ON m.player2_id = p2.id
                LEFT JOIN players pa2 ON m.p2_partner_id = pa2.id
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            $match = $stmt->fetch();
            if (!$match) jsonResponse(['error' => 'Match not found'], 404);
            jsonResponse(['match' => $match]);
        }
        // PUT /matches/{id}/score - Update score (admin)
        elseif ($method === 'PUT' && count($segments) === 3 && $segments[2] === 'score') {
            requireAuth();
            $id = (int)$segments[1];
            $body = getJsonBody();
            $s1 = (int)($body['player1_score'] ?? 0);
            $s2 = (int)($body['player2_score'] ?? 0);
            
            if ($s1 === $s2) jsonResponse(['error' => 'Scores cannot be equal'], 400);
            if (max($s1, $s2) < 11) jsonResponse(['error' => 'At least one player must reach 11'], 400);
            
            $winnerId = $s1 > $s2 ? 'player1_id' : 'player2_id';
            
            // Get match to find winner_id
            $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
            $stmt->execute([$id]);
            $match = $stmt->fetch();
            if (!$match) jsonResponse(['error' => 'Match not found'], 404);
            
            $actualWinner = $s1 > $s2 ? $match['player1_id'] : $match['player2_id'];
            
            $stmt = $pdo->prepare("UPDATE matches SET player1_score = ?, player2_score = ?, winner_id = ?, status = 'completed' WHERE id = ?");
            $stmt->execute([$s1, $s2, $actualWinner, $id]);
            
            jsonResponse(['success' => true, 'winner_id' => $actualWinner]);
        }
    }

    // --- RANKINGS ---
    elseif ($segments[0] === 'rankings') {
        if ($method === 'GET') {
            $stmt = $pdo->query("
                SELECT p.*,
                    (SELECT COUNT(*) FROM matches m WHERE (m.player1_id = p.id OR m.player2_id = p.id OR m.p1_partner_id = p.id OR m.p2_partner_id = p.id) AND m.status = 'completed') as total_matches,
                    (SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player1_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR (m.winner_id = m.player2_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id))) AND m.status = 'completed') as wins,
                    (SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player2_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR (m.winner_id = m.player1_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id))) AND m.status = 'completed') as losses
                FROM players p
                GROUP BY p.id
            ");
            $players = $stmt->fetchAll();
            
            foreach ($players as $k => $p) {
                $players[$k]['points'] = ($p['wins'] * 2) - $p['losses'];
                $players[$k]['win_rate'] = $p['total_matches'] > 0 ? round(($p['wins'] / $p['total_matches']) * 100) : 0;
            }
            
            usort($players, function($a, $b) {
                if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
                if ($a['win_rate'] !== $b['win_rate']) return $b['win_rate'] <=> $a['win_rate'];
                return $b['wins'] <=> $a['wins'];
            });
            
            jsonResponse(['rankings' => $players]);
        }
    }

    // --- DASHBOARD (Admin) ---
    elseif ($segments[0] === 'dashboard') {
        requireAuth();
        if ($method === 'GET') {
            $totalPlayers = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();
            $totalMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE status='completed'")->fetchColumn();
            $pendingMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE status IN ('scheduled','pending')")->fetchColumn();
            $totalSeasons = $pdo->query("SELECT COUNT(*) FROM seasons")->fetchColumn();
            
            jsonResponse([
                'dashboard' => [
                    'total_players' => (int)$totalPlayers,
                    'total_matches' => (int)$totalMatches,
                    'pending_matches' => (int)$pendingMatches,
                    'total_seasons' => (int)$totalSeasons,
                ]
            ]);
        }
    }

    // --- 404 ---
    else {
        jsonResponse(['error' => 'Endpoint not found', 'path' => $path], 404);
    }

} catch (Exception $e) {
    jsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}
