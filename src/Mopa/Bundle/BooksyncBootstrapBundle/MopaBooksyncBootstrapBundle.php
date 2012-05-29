<?php

namespace Mopa\Bundle\BooksyncBootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MopaBooksyncBootstrapBundle extends Bundle
{

	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
