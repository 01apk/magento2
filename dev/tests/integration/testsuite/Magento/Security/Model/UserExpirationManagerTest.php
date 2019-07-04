<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

/**
 * TODO: test logging out sessions
 */
class UserExpirationManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    private $adminSessionInfo;

    /**
     * @var \Magento\Security\Model\UserExpirationManager
     */
    private $userExpirationManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->auth = $this->objectManager->create(\Magento\Backend\Model\Auth::class);
        $this->authSession = $this->objectManager->create(\Magento\Backend\Model\Auth\Session::class);
        $this->auth->setAuthStorage($this->authSession);
        $this->adminSessionInfo = $this->objectManager->create(\Magento\Security\Model\AdminSessionInfo::class);
        $this->userExpirationManager =
            $this->objectManager->create(\Magento\Security\Model\UserExpirationManager::class);
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->auth = null;
        $this->authSession  = null;
        $this->adminSessionInfo  = null;
        $this->userExpirationManager = null;
        $this->objectManager = null;
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testUserIsExpired()
    {
        static::markTestSkipped();
        $adminUserNameFromFixture = 'adminUserExpired';
        $user = $this->loadUserByUsername($adminUserNameFromFixture);
        static::assertTrue($this->userExpirationManager->userIsExpired($user));
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testDeactivateExpiredUsersWithExpiredUser()
    {
        $adminUsernameFromFixture = 'adminUserNotExpired';
        $this->loginUser($adminUsernameFromFixture);
        $user = $this->loadUserByUsername($adminUsernameFromFixture);
        $sessionId = $this->authSession->getSessionId();
        $this->expireUser($user);
        $this->userExpirationManager->deactivateExpiredUsers([$user->getId()]);
        $this->adminSessionInfo->load($sessionId, 'session_id');
        $user->reload();
        $userExpirationModel = $this->loadExpiredUserModelByUser($user);
        static::assertEquals(0, $user->getIsActive());
        static::assertNull($userExpirationModel->getId());
        static::assertEquals(AdminSessionInfo::LOGGED_OUT, (int)$this->adminSessionInfo->getStatus());
        $this->auth->logout();
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testDeactivateExpiredUsersWithNonExpiredUser()
    {
        // TODO: login fails for the second test that tries to log a user in, doesn't matter which test
        // it's trying to create a session for the user ID in the previous test
        $adminUsernameFromFixture = 'adminUserNotExpired';
        $this->loginUser($adminUsernameFromFixture);
        $user = $this->loadUserByUsername($adminUsernameFromFixture);
        $sessionId = $this->authSession->getSessionId();
        $this->userExpirationManager->deactivateExpiredUsers([$user->getId()]);
        $user->reload();
        $userExpirationModel = $this->loadExpiredUserModelByUser($user);
        $this->adminSessionInfo->load($sessionId, 'session_id');
        static::assertEquals(1, $user->getIsActive());
        static::assertEquals($user->getId(), $userExpirationModel->getId());
        static::assertEquals(AdminSessionInfo::LOGGED_IN, (int)$this->adminSessionInfo->getStatus());
        $this->auth->logout();
    }

    /**
     * Test deactivating without inputting a user.
     *
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testDeactivateExpiredUsers()
    {
        static::markTestSkipped();
        $notExpiredUser = $this->loadUserByUsername('adminUserNotExpired');
        $expiredUser = $this->loadUserByUsername('adminUserExpired');
        $this->userExpirationManager->deactivateExpiredUsers();
        $notExpiredUserExpirationModel = $this->loadExpiredUserModelByUser($notExpiredUser);
        $expiredUserExpirationModel = $this->loadExpiredUserModelByUser($expiredUser);

        static::assertNotNull($notExpiredUserExpirationModel->getId());
        static::assertNull($expiredUserExpirationModel->getId());
        $notExpiredUser->reload();
        $expiredUser->reload();
        static::assertEquals($notExpiredUser->getIsActive(), 1);
        static::assertEquals($expiredUser->getIsActive(), 0);
    }

    /**
     * Login the given user and return a user model.
     *
     * @param string $username
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function loginUser(string $username)
    {
        $this->auth->login(
            $username,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
    }

    /**
     * @param $username
     * @return \Magento\User\Model\User
     */
    private function loadUserByUsername(string $username): \Magento\User\Model\User
    {
        /** @var \Magento\User\Model\User $user */
        $user = $this->objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername($username);
        return $user;
    }

    /**
     * Expire the given user and return the UserExpiration model.
     *
     * @param \Magento\User\Model\User $user
     * @throws \Exception
     */
    private function expireUser(\Magento\User\Model\User $user)
    {
        $expireDate = new \DateTime();
        $expireDate->modify('-10 days');
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $this->objectManager->create(\Magento\Security\Model\UserExpiration::class);
        $userExpiration->setId($user->getId())
            ->setExpiresAt($expireDate->format('Y-m-d H:i:s'))
            ->save();
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Security\Model\UserExpiration
     */
    private function loadExpiredUserModelByUser(\Magento\User\Model\User $user): \Magento\Security\Model\UserExpiration
    {
        /** @var \Magento\Security\Model\UserExpiration $expiredUserModel */
        $expiredUserModel = $this->objectManager->create(\Magento\Security\Model\UserExpiration::class);
        $expiredUserModel->load($user->getId());
        return $expiredUserModel;
    }
}
