<?php

namespace Wearesho\Yii2\Token;

use yii\di;
use yii\base;

/**
 * Class ValidationBehavior
 * @package Wearesho\Yii2\Token
 */
class ValidationBehavior extends base\Behavior
{
    /** @var array|string|Repository */
    public $repository = [
        'class' => Repository::class,
    ];

    /**
     * Hash attribute name to load token
     * @var string
     */
    public $hash = 'hash';

    /**
     * Owner attribute name to validate
     * @var string
     */
    public $owner = 'owner';

    /**
     * Token attribute name to validate
     * @var string
     */
    public $token = 'token';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->repository = di\Instance::ensure($this->repository, Repository::class);
    }

    public function events(): array
    {
        return [
            base\Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate(base\ModelEvent $event): bool
    {
        /** @var base\Model $model */
        $model = $event->sender;

        $hash = $model->{$this->hash};
        $token = $this->repository->get($hash ?? "");
        if (is_null($token)) {
            $model->addError($this->hash, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->hash,
            ]));
            return $event->isValid = false;
        }

        if($model->{$this->owner} !== $token->getOwner()) {
            $model->addError($this->owner, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->owner,
            ]));
            return $event->isValid = false;
        }

        if($model->{$this->token} !== $token->getValue()) {
            $model->addError($this->token, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->token,
            ]));
            return $event->isValid = false;
        }

        return true;
    }
}
