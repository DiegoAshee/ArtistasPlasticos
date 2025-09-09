<?php
// app/Controllers/CobroController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Competence.php';
require_once __DIR__ . '/../Models/Cobro.php';

class CobroController extends BaseController
{
    /** Helpers comunes */
    private function ensureSession(): void {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { $this->redirect('login'); }
    }

    private function menu(): array {
        $roleId      = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
        return [$menuOptions, $roleId];
    }

    private function getFiltersFromGet(): array {
        return [
            'q'              => $_GET['q']              ?? '',
            'idPaymentType'  => $_GET['idPaymentType']  ?? '',
            'idContribution' => $_GET['idContribution'] ?? '',
            'from'           => $_GET['from']           ?? '',
            'to'             => $_GET['to']             ?? '',
            'onlyLatest'     => isset($_GET['onlyLatest']) ? 1 : 0,
            'idPartner'      => $_GET['idPartner']      ?? '',   // <-- AÑADIR ESTO
        ];
    }

    private function getPaging(): array {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(5, (int)($_GET['pageSize'] ?? 20)); // ← por defecto 20
        return [$page, $pageSize];
    }

    /** Alias de listado principal → Pagadas */
    public function list(): void { $this->pagadas(); }

    /** Cobros pagados (filtrados + paginados) */
    public function pagadas(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m       = new \Cobro();
        $filters = $this->getFiltersFromGet();
        [$page, $pageSize] = $this->getPaging();

        $total = 0;
        $rows  = $m->searchPagadas($filters, $page, $pageSize, $total);
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        $totalPages = max(1, (int)ceil($total / $pageSize));

        $this->view('cobros/list', [
            'menuOptions' => $menuOptions,
            'currentPath' => 'cobros/list',
            'roleId'      => $roleId,

            'rows'        => $rows,
            'types'       => $types,
            'contribs'    => $contribs,
            'filters'     => $filters,

            'page'        => $page,
            'pageSize'    => $pageSize,
            'total'       => $total,
            'totalPages'  => $totalPages,
        ]);
    }

    /** Cobros debidos (socios sin pago para aportación/tipo) */
    public function debidas(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m       = new \Cobro();
        $filters = $this->getFiltersFromGet();
        [$page, $pageSize] = $this->getPaging();

        $total = 0;
        $rows  = $m->searchDebidas($filters, $page, $pageSize, $total);
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        $totalPages = max(1, (int)ceil($total / $pageSize));

        $this->view('cobros/debidas', [
            'menuOptions' => $menuOptions,
            'currentPath' => 'cobros/debidas',
            'roleId'      => $roleId,

            'rows'        => $rows,
            'types'       => $types,
            'contribs'    => $contribs,
            'filters'     => $filters,

            'page'        => $page,
            'pageSize'    => $pageSize,
            'total'       => $total,
            'totalPages'  => $totalPages,
        ]);
    }

    /** Alta */
    public function create(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m = new \Cobro();
        $partners = $m->allPartners();
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPartner      = (int)($_POST['idPartner'] ?? 0);
            $idPaymentType  = (int)($_POST['idPaymentType'] ?? 0);
            $idContribution = (int)($_POST['idContribution'] ?? 0);
            $paidAmount     = (float)($_POST['paidAmount'] ?? 0);

            if ($idPartner<=0 || $idPaymentType<=0 || $idContribution<=0 || $paidAmount<=0) {
                $error = 'Completa todos los campos con valores válidos.';
                $this->view('cobros/create', [
                    'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
                    'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs, 'error'=>$error
                ]);
                return;
            }

            $id = $m->create($idPartner,$idPaymentType,$idContribution,$paidAmount);
            if ($id) { $_SESSION['success']='Cobro registrado.'; $this->redirect('cobros/list'); return; }

            $error = 'No se pudo registrar.';
            $this->view('cobros/create', [
                'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
                'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs, 'error'=>$error
            ]);
            return;
        }

        $this->view('cobros/create', [
            'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
            'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs
        ]);
    }




    public function debtsApi(): void
{
    $this->startSession();
    header('Content-Type: application/json; charset=utf-8');

    $idPartner = (int)($_GET['idPartner'] ?? 0);
    $idPaymentType = isset($_GET['idPaymentType']) && $_GET['idPaymentType'] !== ''
        ? (int)$_GET['idPaymentType'] : null;

    if ($idPartner <= 0) {
        echo json_encode(['success' => false, 'error' => 'idPartner inválido', 'data' => []]);
        return;
    }

    require_once __DIR__ . '/../Models/Cobro.php';
    $m = new \Cobro();
    $data = $m->debtsByPartnerDetailed($idPartner, $idPaymentType);

    echo json_encode(['success' => true, 'data' => $data]);
}





    



    /** Edición */
    public function edit(int $id): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m   = new \Cobro();
        $row = $m->findById((int)$id);
        if (!$row) { $this->redirect('cobros/list'); return; }

        $partners = $m->allPartners();
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPartner      = (int)($_POST['idPartner'] ?? 0);
            $idPaymentType  = (int)($_POST['idPaymentType'] ?? 0);
            $idContribution = (int)($_POST['idContribution'] ?? 0);
            $paidAmount     = (float)($_POST['paidAmount'] ?? 0);

            if ($idPartner<=0 || $idPaymentType<=0 || $idContribution<=0 || $paidAmount<=0) {
                $error='Completa todos los campos con valores válidos.';
                $this->view('cobros/edit', [
                    'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
                    'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs,'error'=>$error
                ]);
                return;
            }

            $ok = $m->update((int)$id,$idPartner,$idPaymentType,$idContribution,$paidAmount);
            if ($ok) { $_SESSION['success']='Cobro actualizado.'; $this->redirect('cobros/list'); return; }

            $error='No se pudo actualizar.';
            $this->view('cobros/edit', [
                'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
                'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs,'error'=>$error
            ]);
            return;
        }

        $this->view('cobros/edit', [
            'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
            'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs
        ]);
    }

    /** Borrado */
    public function delete(int $id): void
    {
        $this->ensureSession();
        (new \Cobro())->delete((int)$id);
        $this->redirect('cobros/list');
    }



}
