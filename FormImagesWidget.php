<?php

namespace demi\image;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\ActiveRecord;
use yii\widgets\InputWidget;
use yii\web\JsExpression;

/**
 * Виджет отображения загруженного изображения в форме.
 */
class FormImagesWidget extends InputWidget
{
    /* Multiple images params */
    public $images;
    public $thumbnailSize;

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        if (!isset($i18n->translations['image-upload'])) {
            $i18n->translations['image-upload'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
            ];
        }
    }

    public function run()
    {
        /* @var $model ActiveRecord|ImageUploaderBehavior */
        $model = $this->model;
        $classname = get_class($model);
        $modelName = strtolower(substr($classname, strrpos($classname, '\\') + 1));

        /* @var $behavior Im ageUploaderBehavior */
        $behavior = $model->getImageBehavior();

        $wigetId = $this->id;
        $img_hint = '<div class="hint-block">';
        $img_hint .= Yii::t('image-upload', 'Supported formats:').' '.
            $behavior->getImageConfigParam('fileTypes').'<br />';
        $img_hint .= Yii::t('image-upload', 'Maximum file size:').' '.
            ceil($behavior->getImageConfigParam('maxFileSize') / 1024 / 1024).Yii::t('image-upload', 'MB');
        $img_hint .= '</div><!-- /.hint-block -->';

        /* Single image */
        $images = $this->images;

        if (!empty($images)) {
            $img_hint = '';
            foreach ($images as $image) {
                $img_hint .= '<div id="'.$wigetId.'_'.$image->id.'" class="row">';
                $img_hint .= '<div class="col-md-12">';
                $img_hint .= Html::img($image->getImageSrc($this->thumbnailSize), ['class' => 'pull-left uploaded-image-preview']);
                $img_hint .= '<div class="btn-group-vertical pull-left"  style="margin-left: 5px;" role="group">';
                $img_hint .= Html::a(Yii::t('image-upload', 'Delete photo').' <i class="glyphicon glyphicon-trash"></i>', '#',
                  [
                      'onclick' => new JsExpression('
                          if (!confirm(" ' .Yii::t('image-upload', 'Are you sure you want to delete the image?').'")) {
                              return false;
                          }

                          $.ajax({
                              type: "post",
                              cache: false,
                              url: "' .Url::to([$modelName.'/deleteImage', 'id' => $image->id]).'",
                              success: function() {
                                  $("#' .$wigetId.', .field-news-img").remove();
                              }
                          });

                          return false;
                      '),
                      'class' => 'btn btn-danger',
                  ]);

                $img_hint .= '</div><!-- /.btn-group -->';
                $img_hint .= '</div><!-- /.col-md-12 -->';
                $img_hint .= '</div><!-- /.row -->';
            }
        }

        $imgAttr = $behavior->getImageConfigParam('imageAttribute');
        $isMultiple = $behavior->getImageConfigParam('uploadMultiple');

        if (!empty($imageVal)) {
            return $img_hint;
        } else {
            if (!$isMultiple) {
                return Html::activeFileInput($model, $imgAttr).$img_hint;
            } else {
                return Html::activeFileInput($model, $imgAttr.'[]', ['multiple' => true, 'accept' => 'image/*']).$img_hint;
            }
        }
    }
}
