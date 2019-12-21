<?php

namespace Halasz\Support\Support;

interface ISupportFormFactory
{
    /**
     * @return SupportForm
     */
    public function create(): SupportForm;
}
