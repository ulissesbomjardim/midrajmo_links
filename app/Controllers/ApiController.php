<?php

namespace App\Controllers;

use App\Core\Response;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Repositories\LinkRepository;

class ApiController
{
    private $categories;
    private $tags;
    private $links;

    public function __construct(
        CategoryRepository $categories,
        TagRepository $tags,
        LinkRepository $links
    ) {
        $this->categories = $categories;
        $this->tags = $tags;
        $this->links = $links;
    }

    public function dispatch($action, $method)
    {
        try {
            if ($method === 'GET' && $action === 'dashboard_data') {
                $this->dashboardData();
            }

            if ($method === 'POST' && $action === 'create_category') {
                $this->createCategory();
            }

            if ($method === 'POST' && $action === 'create_tag') {
                $this->createTag();
            }

            if ($method === 'POST' && $action === 'create_link') {
                $this->createLink();
            }

            Response::json([
                'success' => false,
                'message' => 'Rota não encontrada.',
            ], 404);
        } catch (\Exception $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function dashboardData()
    {
        Response::json([
            'success' => true,
            'data' => [
                'categories' => $this->categories->listAll(),
                'tags' => $this->tags->listAll(),
                'links' => $this->links->listAll(),
            ],
        ], 200);
    }

    private function createCategory()
    {
        $payload = $this->readInput();
        $name = isset($payload['name']) ? trim($payload['name']) : '';

        if ($name === '') {
            Response::json([
                'success' => false,
                'message' => 'Informe o nome da categoria.',
            ], 422);
        }

        $created = $this->categories->create($name);

        Response::json([
            'success' => true,
            'data' => $created,
        ], 201);
    }

    private function createTag()
    {
        $payload = $this->readInput();
        $name = isset($payload['name']) ? trim($payload['name']) : '';

        if ($name === '') {
            Response::json([
                'success' => false,
                'message' => 'Informe o nome da tag.',
            ], 422);
        }

        $created = $this->tags->create($name);

        Response::json([
            'success' => true,
            'data' => $created,
        ], 201);
    }

    private function createLink()
    {
        $payload = $this->readInput();

        $title = isset($payload['title']) ? trim($payload['title']) : '';
        $url = isset($payload['url']) ? trim($payload['url']) : '';
        $description = isset($payload['description']) ? trim($payload['description']) : '';
        $categoryId = isset($payload['category_id']) && $payload['category_id'] !== '' ? (int) $payload['category_id'] : null;

        if ($title === '' || $url === '') {
            Response::json([
                'success' => false,
                'message' => 'Informe título e URL do link.',
            ], 422);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Response::json([
                'success' => false,
                'message' => 'URL inválida.',
            ], 422);
        }

        $tagIds = $this->normalizeTagIds(isset($payload['tag_ids']) ? $payload['tag_ids'] : []);

        $linkId = $this->links->create([
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'category_id' => $categoryId,
            'tag_ids' => $tagIds,
        ]);

        Response::json([
            'success' => true,
            'data' => [
                'id' => $linkId,
            ],
        ], 201);
    }

    private function readInput()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

        if (stripos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $decoded = json_decode($json, true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    private function normalizeTagIds($tagIds)
    {
        if (!is_array($tagIds)) {
            $tagIds = explode(',', (string) $tagIds);
        }

        $normalized = [];
        foreach ($tagIds as $tagId) {
            $value = (int) $tagId;
            if ($value > 0) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }
}
