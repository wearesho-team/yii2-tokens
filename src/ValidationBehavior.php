<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token;

use yii\di;
use yii\base;

class ValidationBehavior extends base\Behavior
{
    /** @var array|string|Repository */
    public $repository = [
        'class' => Repository::class,
    ];

    /**
     * Hash attribute name to load token
     */
    public string $hash = 'hash';

    /**
     * Owner attribute name to validate
     */
    public string $tokenOwner = 'tokenOwner';

    /**
     * Token attribute name to validate
     */
    public string $token = 'token';

    /** @var string */
    public $type;

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

    /**
     * @throws base\InvalidConfigException
     */
    public function beforeValidate(base\ModelEvent $event): bool
    {
        if (!is_string($this->type)) {
            throw new base\InvalidConfigException(
                "Type have to specified as string"
            );
        }

        /** @var base\Model $model */
        $model = $event->sender;

        $hash = $model->{$this->hash};
        $token = $this->repository->get($hash ?? "");
        if (is_null($token) || $token->getType() !== $this->type) {
            $model->addError($this->hash, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->hash,
            ]));
            return $event->isValid = false;
        }

        if ($model->{$this->tokenOwner} !== $token->getOwner()) {
            $model->addError($this->tokenOwner, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->tokenOwner,
            ]));
            return $event->isValid = false;
        }

        if ($model->{$this->token} !== $token->getValue()) {
            $model->addError($this->token, \Yii::t('yii', '{attribute} is invalid.', [
                'attribute' => $this->token,
            ]));
            return $event->isValid = false;
        }

        return true;
    }
}
