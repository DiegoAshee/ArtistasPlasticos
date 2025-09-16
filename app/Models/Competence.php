<?php
// app/Models/Competence.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class Competence
{
    private $lastError = [];
    
    /**
     * Get the last error that occurred
     * @return array Error information including message and code
     */
    public function getLastError(): array {
        return $this->lastError;
    }
    
    /**
     * Set an error message
     * @param string $message Error message
     * @param int|string $code Error code (optional)
     */
    private function setError(string $message, $code = 0): void {
        $this->lastError = [
            'message' => $message,
            'code' => is_numeric($code) ? (int)$code : 0
        ];
    }
    /**
     * Get all competences from the database
     *
     * @return array Array of competences
     */
    public function listAll(): array
    {
        $db = Database::singleton()->getConnection();
        
        try {
            $sql = "SELECT * FROM `competence` ORDER BY idCompetence ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log('Competence::listAll error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all unique menu options from competences
     * @return array Array of unique menu options
     */
    public function getMenuOptions(): array
    {
        $db = Database::singleton()->getConnection();
        
        try {
            $sql = "SELECT DISTINCT menuOption FROM `competence` WHERE menuOption IS NOT NULL AND menuOption != '' ORDER BY menuOption ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $options = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return is_array($options) ? $options : [];
        } catch (\PDOException $e) {
            error_log('Competence::getMenuOptions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new competence
     *
     * @param string $menuOption The menu option text
     * @return int|false The ID of the created competence or false on failure
     */
    public function create(string $menuOption): int|false
    {
        $db = Database::singleton()->getConnection();
        
        try {
            // First, check if the table exists
            $tableCheck = $db->query("SHOW TABLES LIKE 'competence'");
            if ($tableCheck->rowCount() === 0) {
                $this->setError('La tabla de competencias no existe en la base de datos', 404);
                error_log('Competence table does not exist');
                return false;
            }

            // Check table structure
            $stmt = $db->query("DESCRIBE `competence`");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            error_log('Competence table columns: ' . print_r($columns, true));
            
            $sql = "INSERT INTO `competence` (menuOption) 
                    VALUES (:menuOption)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':menuOption', trim($menuOption), \PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return (int)$db->lastInsertId();
            } else {
                $errorInfo = $stmt->errorInfo();
                $this->setError($errorInfo[2] ?? 'Error desconocido al crear la competencia', $errorInfo[1] ?? 0);
                error_log('SQL Error: ' . print_r($errorInfo, true));
                return false;
            }
        } catch (\PDOException $e) {
            $this->setError('Error de base de datos: ' . $e->getMessage(), $e->getCode());
            error_log('Competence::create error: ' . $e->getMessage());
            error_log('SQL State: ' . $e->getCode());
            error_log('Driver Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->setError('Error inesperado: ' . $e->getMessage(), $e->getCode());
            error_log('Unexpected error in Competence::create: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Devuelve un arreglo PLANO de items del menú a partir del rol:
     * [
     *   ['name'=>'Dashboard','url'=>'dashboard','icon'=>'fas fa-home','section'=>'Principal'],
     *   ...
     * ]
     *
     * Solo se lee "name" de la BD y se mapea a url/icon/section con PHP.
     */
    public function getByRole($roleId): array
    {
        if ($roleId === null || $roleId === '') {
            $roleId = 2; // partner por defecto
        }
        $roleId = (int)$roleId;

        $db = Database::singleton()->getConnection();

        try {
            // Solo pedimos el nombre (menuOption)
            $sql = "
                SELECT
                    c.menuOption AS name
                FROM `permission` p
                INNER JOIN `competence` c ON c.idCompetence = p.idCompetence
                WHERE p.idRol = :role
                ORDER BY c.idCompetence ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':role', $roleId, \PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $items = [];

            foreach ($rows as $row) {
                //antiguo metodo imprime el nombre de la base de datos tal cual
                /*
                $name = trim((string)($row['name'] ?? ''));
                [$url, $icon] = $this->mapNameToRouteAndIcon($name);
                if ($url === '')  { $url  = '#'; }
                if ($icon === '') { $icon = 'fas fa-circle'; }
                */
                
                // Formatear nombres compuestos (ej: "SolicitudesPendientes" → "Solicitudes Pendientes")
                $name = trim((string)($row['name'] ?? ''));
                $displayName = $this->formatMenuName($name);
                
                [$url, $icon] = $this->mapNameToRouteAndIcon($name);
                if ($url === '')  { $url  = '#'; }
                if ($icon === '') { $icon = 'fas fa-circle'; }

                $items[] = [
                    'name'    => ($displayName !== '' ? $displayName : 'Opción'),
                    'url'     => $url,                  // relativo, sin slash inicial
                    'icon'    => $icon,
                    'section' => $this->inferSection($url, $name),
                ];
            }

            // Fallback si no hay nada en BD
            if (empty($items)) {
                $items = $this->fallbackForRole($roleId);
            }
            return $items;
        } catch (\PDOException $e) {
            error_log('Competence::getByRole error: ' . $e->getMessage());
            // Fallback si hay error de BD
            return $this->fallbackForRole($roleId);
        }
    }




    //metodo para que tenga mejor visualizacion de salida las opciones de menu
    // Añade este nuevo método para formatear nombres de menú
    private function formatMenuName(string $name): string
    {
        // Convertir "SolicitudesPendientes" a "Solicitudes Pendientes"
        $formatted = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        
        // Capitalizar palabras
        $formatted = ucwords(strtolower($formatted));
        
        // Casos especiales
        $specialCases = [
            'ci' => 'CI',
            'url' => 'URL',
            'bd' => 'BD',
            'id' => 'ID'
        ];
        
        foreach ($specialCases as $short => $long) {
            $formatted = preg_replace("/\b$short\b/i", $long, $formatted);
        }
        
        return $formatted;
    }












    /**
     * Mapea el texto del menú (name) → [rutaRelativa, icono]
     * (La vista usará u($url) para anteponer BASE_URL.)
     */
    private function mapNameToRouteAndIcon(string $name): array
    {
        $n = mb_strtolower(trim($name), encoding: 'UTF-8');

        switch ($n) {
            case 'dashboard':
                return ['dashboard', 'fas fa-home'];

            case 'analytics':
            case 'analitica':
            case 'analíticas':
            case 'analiticas':
                return ['analytics', 'fas fa-chart-line'];

            case 'socios':
            case 'partners':
                return ['partner/list', 'fas fa-users'];

            case 'nuevo socio':
            case 'alta socio':
                return ['partner/create', 'fas fa-user-plus'];

            case 'usuarios':
            case 'users':
                return ['users/list', 'fas fa-users'];
            case 'contribución':
            case 'contribution':
                return ['contribution/list', 'fas fa-users'];

            case 'permisos':
            case 'permissions':
            case 'gestión de permisos':
                return ['permissions', 'fas fa-user-lock'];

            case 'roles':
                return ['role/list', 'fas fa-user-shield'];

            case 'historial pagos':
            case 'historial de pagos':
                return ['partner/manage', 'fas fa-history'];

            case 'reportes':
            case 'reporting':
                return ['reportes', 'fas fa-file-invoice'];

            case 'configuracion':
            case 'configuración':
            case 'ajustes':
            case 'settings':
                return ['configuracion', 'fas fa-cog'];

            case 'backup':
            case 'respaldo':
                return ['backup', 'fas fa-database'];

            case 'mi perfil':
            case 'perfil':
            case 'profile':
                return ['users/profile', 'fas fa-user'];

            case 'ayuda':
            case 'help':
                return ['ayuda', 'fas fa-question-circle'];

            case 'mis pagos':
            case 'pagos':
                return ['partner/payment-history', 'fas fa-file-invoice'];
            case 'pagos pendientes':
                return ['partner/pending-payments', 'fas fa-file-invoice'];
            // Agregamos la opción para "competencias"
            case 'competencias':
                return ['competence/competence_list', 'fas fa-cogs'];

            // === NUEVOS alias para la bandeja de solicitudes ===
            case 'solicitudespedientes':       // sin espacio
            case 'solicitudespendientes':      // por si acaso
            case 'pendientes de socios':
                return ['partnerOnline/pending', 'fas fa-inbox'];
            case 'revisar pagos':
                return ['admin/review-payments', 'fas fa-file-invoice'];

            //Cobros 
            // mapNameToRouteAndIcon()
            case 'cobros':
            case 'recibos':
            case 'ingresos':
                return ['cobros/socios', 'fas fa-receipt'];
            case 'movimiento':
                return ['movement/list', 'fas fa-file-invoice'];

            //Cobros 
            // mapNameToRouteAndIcon()
            case 'Conceptos':
            case 'Concept':
            case 'conceptos':
                return ['conceptos/list', 'fas fa-receipt'];
            case 'concept':
                return ['movement/list', 'fas fa-file-invoice'];


            default:
                // Si aparece algo desconocido, devuelve link inerte
                return ['', 'fas fa-circle'];
        }
    }

    /**
     * Intenta colocar cada ítem en una sección del sidebar.
     */
    private function inferSection(string $url, string $name): string
    {
        $u = ltrim(strtolower($url), '/');
        $n = mb_strtolower(trim($name), 'UTF-8');

        if ($u === 'dashboard' || $u === 'analytics') {
            return 'Principal';
        }



         // ← añade esta línea para partneronline/*
        if ($u === 'partneronline/pending' || strpos($u, 'partneronline/') === 0) {
            return 'Gestión';
        }

        //cobros
        if (strpos($u, 'cobros/') === 0) {
            return 'Gestión';
        }
        

        if (strpos($u, 'partner/') === 0 || strpos($u, 'users/list') === 0 || $n === 'usuarios') {
            return 'Gestión';
        }
        if (in_array($u, ['configuracion','backup','ayuda','users/profile'], true)) {
            return 'Sistema';
        }
        // Desconocidos → a Gestión por defecto
        return 'Gestión';
    }

    /**
     * Menú por defecto si BD no devuelve nada.
     */
    private function fallbackForRole(int $roleId): array
    {
        if ($roleId === 1) { // admin
            return [
                ['name'=>'Dashboard',    'url'=>'dashboard',      'icon'=>'fas fa-home',        'section'=>'Principal'],
                ['name'=>'Socios',       'url'=>'partner/list',   'icon'=>'fas fa-users',       'section'=>'Gestión'],
                ['name'=>'Nuevo Socio',  'url'=>'partner/create', 'icon'=>'fas fa-user-plus',   'section'=>'Gestión'],
                ['name'=>'Mi Perfil',    'url'=>'users/profile',  'icon'=>'fas fa-user',        'section'=>'Sistema'],
                ['name'=>'Ayuda',        'url'=>'ayuda',          'icon'=>'fas fa-question-circle','section'=>'Sistema'],
            ];
        }
        // partner
        return [
            ['name'=>'Dashboard', 'url'=>'dashboard',     'icon'=>'fas fa-home',  'section'=>'Principal'],
            ['name'=>'Socios',    'url'=>'partner/list',  'icon'=>'fas fa-users', 'section'=>'Gestión'],
            ['name'=>'Mi Perfil', 'url'=>'users/profile', 'icon'=>'fas fa-user',  'section'=>'Sistema'],
            ['name'=>'Ayuda',     'url'=>'ayuda',         'icon'=>'fas fa-question-circle','section'=>'Sistema'],
        ];
    }
}
