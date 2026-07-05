<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Engine\Request;
use Engine\Response;
use App\Repository\ContentPageRepository;
use App\Schema\ContentPageSchema;
use App\Security\Csrf;

final class ContentPageController extends AdminController
{
    public function index(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        $repo       = new ContentPageRepository($this->db());
        $categories = ContentPageSchema::categories();

        $rootsByCategory = [];
        $allRootIds      = [];
        foreach ($categories as $category => $label) {
            $roots = $repo->findRootsByCategory($category);
            $rootsByCategory[$category] = $roots;
            foreach ($roots as $root) {
                $allRootIds[] = (int)$root['id'];
            }
        }

        return $this->render('templates/admin/content-pages/index', [
            'activeNav'       => 'content-pages',
            'categories'      => $categories,
            'rootsByCategory' => $rootsByCategory,
            'childrenByRoot'  => $repo->findChildrenGrouped($allRootIds),
            'csrfToken'       => Csrf::token(),
        ]);
    }

    public function new(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        return $this->form(null, []);
    }

    public function edit(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        $page = (new ContentPageRepository($this->db()))->findById((int)($params['id'] ?? 0));
        if ($page === null) {
            return $this->redirect('/admin/content-pages');
        }

        return $this->form($page, []);
    }

    public function create(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        return $this->save($request, null);
    }

    public function update(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        $repo = new ContentPageRepository($this->db());
        $page = $repo->findById((int)($params['id'] ?? 0));
        if ($page === null) {
            return $this->redirect('/admin/content-pages');
        }

        return $this->save($request, $page);
    }

    public function delete(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        if (Csrf::verify((string)$request->post('csrf_token'))) {
            (new ContentPageRepository($this->db()))->delete((int)$request->post('id'));
        }

        return $this->redirect('/admin/content-pages');
    }

    /**
     * The Quill editor's image button posts here directly; response is the
     * URL it should insert into the document.
     */
    public function uploadImage(Request $request, array $params = []): Response
    {
        if ($redirect = $this->ensureAuthenticated()) {
            return $redirect;
        }

        $file = $request->files['image'] ?? null;
        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Upload failed.'], 400);
        }

        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $mime    = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ext     = array_search($mime, $allowed, true);

        if ($ext === false || $file['size'] > 8 * 1024 * 1024) {
            return $this->json(['error' => 'Only jpg/png/webp/gif images up to 8MB are allowed.'], 400);
        }

        $dir = BASE_PATH . '/uploads/content-pages';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return $this->json(['error' => 'Could not save the file.'], 500);
        }

        return $this->json(['url' => '/uploads/content-pages/' . $filename]);
    }

    private function form(?array $page, array $errors): Response
    {
        $repo       = new ContentPageRepository($this->db());
        $categories = ContentPageSchema::categories();

        $rootsByCategory = [];
        foreach ($categories as $category => $label) {
            $rootsByCategory[$category] = $repo->findRootsByCategory($category, $page['id'] ?? null);
        }

        return $this->render('templates/admin/content-pages/form', [
            'activeNav'       => 'content-pages',
            'page'            => $page,
            'categories'      => $categories,
            'rootsByCategory' => $rootsByCategory,
            'errors'          => $errors,
            'csrfToken'       => Csrf::token(),
        ]);
    }

    private function save(Request $request, ?array $existing): Response
    {
        $repo = new ContentPageRepository($this->db());

        if (!Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->form($existing, ['form' => 'Your session expired, please try again.']);
        }

        $title      = trim((string)$request->post('title'));
        $category   = (string)$request->post('category');
        $body       = (string)$request->post('body');
        $status     = (string)$request->post('status');
        $parentRaw  = $request->post('parent_id');
        $parentId   = ($parentRaw !== null && $parentRaw !== '') ? (int)$parentRaw : null;

        $errors = [];
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }
        if (!array_key_exists($category, ContentPageSchema::categories())) {
            $errors['category'] = 'Pick a valid category.';
        }
        if (!in_array($status, [ContentPageSchema::STATUS_DRAFT, ContentPageSchema::STATUS_PUBLISHED], true)) {
            $errors['status'] = 'Pick a valid status.';
        }
        if ($parentId !== null) {
            $parent = $repo->findById($parentId);
            if ($parent === null || $parent['category'] !== $category || (int)$parent['id'] === (int)($existing['id'] ?? 0)) {
                $errors['parent_id'] = 'Pick a valid parent in the same category.';
            }
        }

        if (!empty($errors)) {
            return $this->form($existing, $errors);
        }

        $slugBase = $repo->slugify($title);
        $slug     = $slugBase;
        $suffix   = 2;
        while ($repo->slugExists($slug, $existing['id'] ?? null)) {
            $slug = $slugBase . '-' . $suffix++;
        }

        $data = [
            'parent_id' => $parentId,
            'category'  => $category,
            'title'     => $title,
            'slug'      => $slug,
            'body'      => $body,
            'status'    => $status,
        ];

        if ($existing !== null) {
            $repo->update((int)$existing['id'], $data);
        } else {
            $repo->create($data);
        }

        return $this->redirect('/admin/content-pages');
    }

    private function json(array $data, int $status = 200): Response
    {
        return (new Response())
            ->setStatusCode($status)
            ->setContent(json_encode($data))
            ->addHeader('Content-Type', 'application/json');
    }
}
