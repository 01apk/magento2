<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

/**
 * Admin User Expiration model.
 */
class UserExpiration extends \Magento\Framework\Model\AbstractModel implements UserExpirationInterface
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Security\Model\ResourceModel\UserExpiration::class);
    }

    /**
     * `expires_at` getter.
     *
     * @return string
     */
    public function getExpiresAt()
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * `expires_at` setter.
     *
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * `user_id` getter.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * `user_id` setter.
     *
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }
}
