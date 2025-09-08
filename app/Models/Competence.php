<?php
// app/Models/Competence.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class Competence
{
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
                return ['partner/payments', 'fas fa-file-invoice'];
            
            // Agregamos la opción para "competencias"
            case 'competencias':
                return ['competence/list', 'fas fa-cogs'];

            // === NUEVOS alias para la bandeja de solicitudes ===
            case 'solicitudespedientes':       // sin espacio
            case 'solicitudespendientes':      // por si acaso
            case 'pendientes de socios':
                return ['partnerOnline/pending', 'fas fa-inbox'];

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
