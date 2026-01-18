<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Controller;

use themes\Wordpress\Framework\Core\Controller;

class DontEnqueueWithoutEnqueuableController extends Controller
{
    public function enqueue()
    {
    }
}
