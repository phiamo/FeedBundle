<?php

namespace Mopa\Bundle\BooksyncAdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MopaBooksyncAdminBundle extends Bundle
{
        public function getParent()
        {
            return 'SonataAdminBundle';
        }

}
