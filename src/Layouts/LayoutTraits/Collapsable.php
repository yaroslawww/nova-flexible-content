<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

trait Collapsable
{
    protected bool $collapsed = false;

    public function setCollapsed(bool $collapsed = true): static
    {
        $this->collapsed = $collapsed;

        return $this;
    }

    public function isCollapsed(): bool
    {
        return $this->collapsed;
    }
}
