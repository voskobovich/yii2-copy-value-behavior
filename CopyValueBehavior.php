<?php

namespace voskobovich\behaviors;

use yii\base\Behavior;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;


/**
 * Class CopyValueBehavior
 * @package voskobovich\behaviors
 */
class CopyValueBehavior extends Behavior
{
    /**
     * Attributes list
     * Format:
     *  [
     *      'meta_title' => 'name'
     *          or
     *      'meta_title' => [
     *          'attribute' => 'name',       -- required
     *          'clearTags' => true \ false, -- NOT required
     *          'maxLength' => integer       -- NOT required
     *      ]
     *  ]
     * @var
     */
    public $attributes;

    /**
     * Clear from html tags?
     * @var bool
     */
    public $clearTags = false;

    /**
     * Cut to a specified number of characters?
     * @var bool|integer
     */
    public $maxLength = false;

    /**
     * @var bool
     */
    public $forceCopy = false;

    /**
     * Init
     */
    public function init()
    {
        if ($this->attributes == null) {
            throw new InvalidConfigException('Property "attributes" must be set');
        }
        parent::init();
    }

    /**
     * Events
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'copyAttributes',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'copyAttributes',
        ];
    }

    /**
     * Processing
     * @throws Exception
     */
    function copyAttributes()
    {
        $owner = $this->owner;

        foreach ($this->attributes as $destination => $source) {
            if (is_array($source)) {

                if (empty($source['attribute'])) {
                    throw new Exception('Where to get the data? Set key "attribute"!');
                }

                $sourceAttribute = $source['attribute'];
                $clearTags = (bool)isset($source['clearTags']) ? $source['clearTags'] : $this->clearTags;
                $maxLength = isset($source['maxLength']) ? $source['maxLength'] : $this->maxLength;

                if ((empty($owner->{$destination}) || $this->forceCopy) && !empty($owner->{$sourceAttribute})) {
                    $value = $owner->{$sourceAttribute};

                    $value = $clearTags ? strip_tags($value) : $value;
                    $value = is_integer($maxLength) ? mb_substr($value, 0, $maxLength) : $value;

                    $owner->{$destination} = $value;
                }
            } else {
                if ((empty($owner->{$destination}) || $this->forceCopy) && !empty($owner->{$source})) {

                    $value = $owner->{$source};

                    $value = $this->clearTags ? strip_tags($value) : $value;
                    $value = is_integer($this->maxLength) ? mb_substr($value, 0, $this->maxLength) : $value;

                    $owner->{$destination} = $value;
                }
            }
        }
    }
}