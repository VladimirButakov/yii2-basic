<?php

namespace app\models;

use Yii;
use app\models\Category;
use app\models\Tag;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $content
 * @property string|null $date
 * @property string|null $image
 * @property int|null $viewed
 * @property int|null $user_id
 * @property int|null $status
 * @property int|null $category_id
 *
 * @property ArticleTag[] $articleTags
 * @property Comment[] $comments
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required' ],
            [['title', 'description', 'content'], 'string'],
            [['date'], 'date', 'format'=>'php:Y-m-d'],
            [['date'], 'default', 'value'=>date('Y-m-d')],
            [['title'], 'string', 'max'=>255]

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'date' => 'Date',
            'image' => 'Image',
            'viewed' => 'Viewed',
            'user_id' => 'User ID',
            'status' => 'Status',
            'category_id' => 'Category ID',
        ];
    }

     /**
     * Сохранение файла в бд
     * 
     * @param string $filename название файла
     * @return bool
     */
    public function saveImage(string $filename) 
    {
        $this->image = $filename;
        return (bool) $this->save(false);
    }

    /**
     * Выводим картинку
     * 
     * @return string
     */
    public function getImage() : string
    {
        
        return (isset($this->image)) ? '/uploads/' . $this->image : '/no-image.png';
    }

    /**
     * Удалаяем изображение
     */
    public function deleteImage()
    {
        $imageUploadModel = new ImageUpload();
        $imageUploadModel->deleteCurrentImage($this->image);
    }

    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete();
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Сохраняем категорию в бд
     * 
     * @param int $categoryId
     * @return bool
     */
    public function saveCategory(int $categoryId)
    {
        $category = Category::findOne($categoryId);
        $result = isset($category);
        if ($result)
        {
            $this->link('category', $category);
        }

        return $result; 
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('article_tag', ['article_id' => 'id']);
    }

    public function getSelectedeTags()
    {
        $selectedTags = $this->getTags()->select('id')->asArray()->all();
        return ArrayHelper::getColumn( $selectedTags, 'id');
    }

    /**
     * Сохраняем теги статьи
     * 
     * @return void
     */
    public function saveTags(array $tags)
    {
        ArticleTag::deleteAll(['article_id'=>$this->id]);
        
        foreach($tags as $tagId)
        {
            $tag = Tag::findOne($tagId);
            $this->link('tags', $tag);
        }
    }

    /**
     * Сохраняем теги статьи
     * 
     * @param int $pageCoutn
     * @return string
     */
    public function getDate()
    {
        return Yii::$app->formatter->asDate($this->date);
    }

    /**
     *  Возвращает статьи и пагинацию
     * 
     * @return array
     */
    public static function getAll(int $pageSize = 1)
    {
        // build a DB query to get all articles with status = 1
        $query = Article::find();

        // get the total number of articles (but do not fetch the article data yet)
        $count = $query->count();

        // create a pagination object with the total count
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);

        // limit the query using the pagination and retrieve the articles
        $articles = $query->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();

        $data = [
            'articles' => $articles,
            'pagination' => $pagination,
        ];

        return  $data;
    }
}
