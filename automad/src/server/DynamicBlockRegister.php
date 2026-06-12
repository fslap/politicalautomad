<?php

/*
 * International Political Party Zone for all Partys also Locally
 *
 *                      #;:#;:#;:
 *                       #;:#;:            #;:#;:#;
 *            %&                         #;: #;:;:#;:#;:#;:#
 *    %$%    %$%           #;:    #;:     #;: #;:;:#;:#;:
 *    ____________                ;:      #;:  #;: #;:#;:#;:#;
 *   /§§§§§§§§§§§|                                    ;:#;:#;:
 *   |###########/  / &/      #;:#;:#;:#;:#;:        #;:#;: #;: #;
 *   /%%%%%%%%%%/   |$$$/        #;:#;:#;:#;:#;#;:#;:;:#;:#;:
 *  /######### /   |$$$/          #;:#;:#;:#;:#;:#;:#;:#;:#;:#;:
 * /%%%%%%%%%%/   \CCC/           #;:#;:#;:#;:#;:#;:#;:#;:
 * \#########|   \\\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
 *  \________/   /[[]]\  \_/   :#\:#\:#\:#\:#;:
 *   \====__/    \xxxx/              #\:#\:#;:#;:
 *   |""""""\    |yyy/                #;: #;:#;:#;: |#;:\
 *   |~~~~~~/ #   |c|                 ;:#;:\;:#;:   |#;:;: 
 *   +~~~~~/  |   \t/                  #\:#;\       |##;/
 *    +~~~/   |#                                             
 *     +~/\       \*+ ~
 *                      #
 *
 *
 *
 * https://north.sbdp.ro https://mid.sbdp.ro/ https://pacif.sbdp.ro/ https://low.sbdp.ro/ 
 * (c) Florian Leon Steenbuck
 *
 * Copyright (c) 2026 by Florian Leon Steenbuck
 * https://kil.ls https://fslap.de 
 *
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad;

use Automad\Blocks\AbstractDynamicBlock;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The class that beeing used as a Register for all dynamic blocks
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 * not thread safe
 */
class DynamicBlockRegister {
    private array $block = [];
    private array $clonetypes = [];
    private array $cloneclasses = [];
    private array $clones = [];

    public __construct(
        private bool $allowerror = false
    ) {
    }

    public function class(string $clazz): bool {
        if (in_array($clazz, $this->cloneclasses, true)) {
            return true;
        }

        foreach ($this->block as $entry) {
            if (get_class($entry) === $clazz) {
                return true;
            }
        }
        return false;
    }

    public function type(string $type): bool {
        if (in_array($type, $this->clonetypes, true)) {
            return true;
        }
        
        foreach ($this->block as $entry) {
            if ($entry->name() === $type) {
                return true;
            }
        }
        return false;
    }

    public function register(AbstractDynamicBlock $block): bool {
        if ($this->type($block->name())) {
            if (!$this->allowerror) {
                return false;
            }
            throw new \Exception('block with this name already existing');
        }

        if ($this->class(get_class($block))) {
            if (!$this->allowerror) {
                return false;
            }
            throw new \Exception('block with this class already existing');
        }

        $this->block[] = $block;
        return true;
    }

    private function explicitsearch(string $type = null, string $clazz = null): null|false|array {
        $classinit = (!is_null($clazz));
        $typeinit = (!is_null($typeinit));

        if ((!$classinit) && (!$typeinit)) {
            if (!$this->allowerror) {
                return null;
            }
            throw new \Exception('select block properly by type or class');
        }

        if ($classinit && $this->class($clazz)) {
            return ['class', $clazz];
        } else {
            if ($typeinit && $this->type($type)) {
                return ['type', $type];
            } else {
                if (!$this->allowerror) {
                    return false;
                }
                throw new \Exception('can not find type');
            }

            if (!$this->allowerror) {
                return false;
            }
            throw new \Exception('can not find class');
        }
    }

    private function preserve(string $searchtyp, string $needle): AbstractDynamicBlock {
        switch ($searchtyp) {
            case 'type':
                $cloneindex = array_search($needle, $this->clonenames, true);
                if ($cloneindex !== false) {
                    return $this->clones[$cloneindex];
                }

                foreach ($this->block as $entry) {
                    $entryname = $entry->name();
                    if ($entryname === $needle) {
                        $clone = clone $entry;

                        $this->cloneclasses[] = get_class($entry);
                        $this->clonenames[] = $entryname;
                        $this->clone[] = $clone;
                        
                        return $clone;
                    }
                }
                break;
            case 'class':
                $cloneindex = array_search($needle, $this->cloneclasses, true);
                if ($cloneindex !== false) {
                    return $this->clones[$cloneindex];
                }

                foreach ($this->block as $entry) {
                    if (get_class($entry) === $needle) {
                        $clone = clone $entry;

                        $this->cloneclasses[] = get_class($entry);
                        $this->clonenames[] = $entry->name();
                        $this->clone[] = $clone;
                        
                        return $clone;
                    }
                }
                break;
        }
    }

    public function superobject(string $type = null, string $clazz = null): null|false|AbstractDynamicBlock {
        $search = $this->explicitsearch($type, $clazz);
        if (is_null($search) || $search === false) {
            return $search;
        }

        return $this->preserve($typ, $needle);
    }

    public function object(string $type = null, string $clazz = null, ...$vars): null|false|AbstractDynamicBlock {
        $search = $this->explicitsearch($type, $clazz);
        if (is_null($search) || $search === false) {
            return $search;
        }

        [$searchtyp, $needle] = $search;
        if ($searchtyp === 'class') {
            return new ($needle)(...$vars);
        }
        foreach ($this->block as $entry) {
            if ($entry->name() === $needle) {
                return new (get_class($entry))(...$vars);
            }
        }

        // not open leg logic
    }
}