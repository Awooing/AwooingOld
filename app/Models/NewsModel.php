<?php


namespace Awoo\Models;


use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class NewsModel
{
    /** @var Context */
    private $database;

    public function __construct(Context $db)
    {
        $this->database = $db;
    }

    /**
     * Returns Articles or null if the table doesn't exist
     * @return Selection|null
     */
    public function getArticles(): ?Selection
    {
        return $this->database->table("awoo_posts");
    }

    /**
     * Returns the Post with id in param $id,
     * or null if the post doesn't exist
     * @param $id
     * @return ActiveRow|null
     */
    public function getArticleById($id): ?ActiveRow
    {
        return $this->getArticles()->get($id);
    }

    /**
     * Returns Posts by author with user id in param $uid,
     * or returns null if the table doesn't exist or there are
     * no posts from that particular author
     * @param $uid
     * @return Selection|null
     */
    public function getArticlesByAuthor($uid): ?Selection
    {
        return $this->getArticles()->where("user_id = ?", $uid);
    }

    public function getArticlesByDate($)

}