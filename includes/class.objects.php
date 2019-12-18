<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            //parent::__construct('user', array('nid', 'username', 'password', 'level','email','ip', 'regdate'), $id);
             //parent::__construct('user', true), $id);
        }
    }
