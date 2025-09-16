<?php
require_once __DIR__ . '/Database.php';

class User
{
    // ✅ Fixed parameter binding issue
    public static function findByEmailOrEmployeeId($identifier)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE email = ? OR employee_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByEmail($email)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findById($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByEmployeeId($employeeId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE employee_id = ? LIMIT 1");
        $stmt->execute([$employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create($email, $password, $role = 'student', $employeeId = null, $name = null)
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, employee_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $name ?? $email, // Use email as name if name not provided
            $email,
            $hash,
            $role,
            $employeeId
        ]);
    }

    public static function updatePassword($userId, $newPassword)
    {
        $db = Database::getInstance();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    }

    public static function updateProfile($userId, $data)
    {
        $db = Database::getInstance();
        
        $fields = [];
        $values = [];
        $allowedFields = ['name', 'email', 'phone', 'department', 'strand', 'year_level'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $values[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        
        return $stmt->execute($values);
    }

    public static function emailExists($email, $excludeId = null)
    {
        $db = Database::getInstance();
        
        if ($excludeId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        
        return $stmt->fetchColumn() > 0;
    }

    public static function employeeIdExists($employeeId, $excludeId = null)
    {
        if (empty($employeeId)) {
            return false;
        }
        
        $db = Database::getInstance();
        
        if ($excludeId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE employee_id = ? AND id != ?");
            $stmt->execute([$employeeId, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE employee_id = ?");
            $stmt->execute([$employeeId]);
        }
        
        return $stmt->fetchColumn() > 0;
    }

    public static function getAllUsers($role = null)
    {
        $db = Database::getInstance();
        
        if ($role) {
            $stmt = $db->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus($userId, $status)
    {
        $allowedStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $userId]);
    }

    public static function delete($userId)
    {
        $db = Database::getInstance();
        
        // Don't actually delete, just mark as inactive
        $stmt = $db->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public static function getStats()
    {
        try {
            $db = Database::getInstance();
            
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
                    SUM(CASE WHEN role = 'faculty' THEN 1 ELSE 0 END) as faculty,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN role = 'librarian' THEN 1 ELSE 0 END) as librarians,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
                FROM users
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'students' => 0,
                'faculty' => 0,
                'admins' => 0,
                'librarians' => 0,
                'active' => 0
            ];
            
        } catch (Exception $e) {
            error_log("User stats error: " . $e->getMessage());
            return [
                'total' => 0,
                'students' => 0,
                'faculty' => 0,
                'admins' => 0,
                'librarians' => 0,
                'active' => 0
            ];
        }
    }
}