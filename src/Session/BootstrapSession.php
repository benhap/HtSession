<?php

namespace HtSession\Session;

use Zend\Session\Container as SessionContainer;
use Zend\Session\SessionManager;

class BootstrapSession {

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * Constructor
     *
     * @param  SessionManager $sessionManager
     * @return void
     */
    public function __construct(SessionManager $sessionManager) {
        $this->sessionManager = $sessionManager;
    }

    /**
     * gets SessionManager instance
     *
     * @return SessionManager
     */
    public function getSessionManager() {
        return $this->sessionManager;
    }

    /**
     * Initializes sesssion
     * 
     * If you are storing your session data in a database you have to manually update the session_id in the database. The session_set_save_handler() will not do it for you.
     * Be sure to set session_regenerate_id() to FALSE since it's not really necessary to delete the whole record from MySQL and add it again. That's unnecessary overhead. Only changing the id matters.
     *
     * @return void
     */
    public function bootstrap() {

        $this->getSessionManager()->start();
        $container = new SessionContainer('initialized');
        if (!isset($container->init)) {

            if ($this->getSessionManager()->getSaveHandler() instanceof \HtSession\Session\SaveHandler\DoctrineDbal) {
                $oldSessionId = $this->getSessionManager()->getId();
                $this->getSessionManager()->regenerateId(false);
                $newSessionId = $this->getSessionManager()->getId();

                $saveHandler = $this->getSessionManager()->getSaveHandler();

                $this->UpdateSessID($saveHandler, $oldSessionId, $newSessionId);
            } else {
                $this->getSessionManager()->regenerateId(true);
            }

            $container->init = 1;
        }
    }

    public function UpdateSessID(\HtSession\Session\SaveHandler\DoctrineDbal $saveHandler, $oldSessionId, $newSessionId) {
        $data = array(
            $saveHandler->getOptions()->getIdColumn() => $newSessionId,
        );

        $id = array(
            $saveHandler->getOptions()->getIdColumn() => $oldSessionId,
        );

        $saveHandler->getConnection()->update($saveHandler->getOptions()->getTableName(), $data, $id);

        return true;
    }

}
