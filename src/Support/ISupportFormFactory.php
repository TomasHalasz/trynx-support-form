<?php

namespace Halasz\Support;

interface ISupportFormFactory
{
    /**
     * @return SupportForm
     */
    public function create(): SupportForm;
}
