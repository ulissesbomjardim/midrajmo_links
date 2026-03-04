<?php

namespace App\Repositories;

use PDO;

class LinkRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listAll()
    {
        $sql = 'SELECT l.id, l.title, l.url, l.description, l.category_id, l.created_at, c.name AS category_name
                FROM links l
                LEFT JOIN categories c ON c.id = l.category_id
                ORDER BY l.created_at DESC';

        $stmt = $this->pdo->query($sql);
        $links = $stmt->fetchAll();

        if (!$links) {
            return [];
        }

        $linkIds = [];
        foreach ($links as $link) {
            $linkIds[] = (int) $link['id'];
        }

        $tagsByLinkId = $this->loadTagsByLinkIds($linkIds);

        foreach ($links as &$link) {
            $id = (int) $link['id'];
            $link['id'] = $id;
            $link['category_id'] = $link['category_id'] !== null ? (int) $link['category_id'] : null;
            $link['tags'] = isset($tagsByLinkId[$id]) ? $tagsByLinkId[$id] : [];
        }

        return $links;
    }

    public function create($payload)
    {
        $tagIds = isset($payload['tag_ids']) ? $payload['tag_ids'] : [];

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO links (title, url, description, category_id) VALUES (:title, :url, :description, :category_id)'
            );

            $stmt->execute([
                'title' => $payload['title'],
                'url' => $payload['url'],
                'description' => $payload['description'],
                'category_id' => $payload['category_id'],
            ]);

            $linkId = (int) $this->pdo->lastInsertId();

            if (!empty($tagIds)) {
                $linkTagStmt = $this->pdo->prepare(
                    'INSERT INTO link_tags (link_id, tag_id) VALUES (:link_id, :tag_id)'
                );

                foreach ($tagIds as $tagId) {
                    $linkTagStmt->execute([
                        'link_id' => $linkId,
                        'tag_id' => (int) $tagId,
                    ]);
                }
            }

            $this->pdo->commit();

            return $linkId;
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    private function loadTagsByLinkIds($linkIds)
    {
        if (empty($linkIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($linkIds), '?'));

        $sql = "SELECT lt.link_id, t.id, t.name
                FROM link_tags lt
                INNER JOIN tags t ON t.id = lt.tag_id
                WHERE lt.link_id IN ($placeholders)
                ORDER BY t.name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($linkIds);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $linkId = (int) $row['link_id'];
            if (!isset($result[$linkId])) {
                $result[$linkId] = [];
            }
            $result[$linkId][] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
            ];
        }

        return $result;
    }
}
