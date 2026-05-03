<?php
class SessionHandlerDB implements SessionHandlerInterface
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName)
    {
        return true; // Return true for success
    }

    public function close()
    {
        return true; // Return true for success
    }

    public function read($id)
    {
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id AND (UNIX_TIMESTAMP(timestamp) + lifetime) > :now');
        $stmt->execute(['id' => $id, 'now' => time()]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data)
    {
        $stmt = $this->pdo->prepare('REPLACE INTO sessions (id, data, lifetime, timestamp) VALUES (:id, :data, :lifetime, FROM_UNIXTIME(:timestamp))');
        return $stmt->execute(['id' => $id, 'data' => $data, 'lifetime' => ini_get('session.gc_maxlifetime'), 'timestamp' => time()]);
    }

    public function destroy($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function gc($maxlifetime)
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE (UNIX_TIMESTAMP(timestamp) + lifetime) < :now');
        return $stmt->execute(['now' => time()]);
    }

    public function create_sid()
    {
        return bin2hex(openssl_random_pseudo_bytes(16)); // Genera un ID di sessione univoco
    }
}