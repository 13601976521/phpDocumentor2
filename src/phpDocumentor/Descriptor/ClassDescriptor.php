<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @copyright 2010-2013 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Descriptor;

/**
 * Descriptor representing a Class.
 */
class ClassDescriptor extends DescriptorAbstract implements Interfaces\ClassInterface
{
    /** @var ClassDescriptor|null $extends Reference to an instance of the superclass for this class, if any. */
    protected $parent;

    /** @var Collection $implements References to interfaces that are implemented by this class. */
    protected $implements;

    /** @var boolean $abstract Whether this is an abstract class. */
    protected $abstract = false;

    /** @var boolean $final Whether this class is marked as final and can't be subclassed. */
    protected $final = false;

    /** @var Collection $constants References to constants defined in this class. */
    protected $constants;

    /** @var Collection $properties References to properties defined in this class. */
    protected $properties;

    /** @var Collection $methods References to methods defined in this class. */
    protected $methods;

    /**
     * Initializes the all properties representing a collection with a new Collection object.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInterfaces(new Collection());
        $this->setConstants(new Collection());
        $this->setProperties(new Collection());
        $this->setMethods(new Collection());
    }

    /**
     * {@inheritDoc}
     */
    public function setParent($parents)
    {
        $this->parent = $parents;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritDoc}
     */
    public function setInterfaces(Collection $implements)
    {
        $this->implements = $implements;
    }

    /**
     * {@inheritDoc}
     */
    public function getInterfaces()
    {
        return $this->implements;
    }

    /**
     * {@inheritDoc}
     */
    public function setFinal($final)
    {
        $this->final = $final;
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * {@inheritDoc}
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * {@inheritDoc}
     */
    public function setConstants(Collection $constants)
    {
        $this->constants = $constants;
    }

    /**
     * {@inheritDoc}
     */
    public function getConstants($includeInherited = true)
    {
        if (!$includeInherited || !$this->getParent() || (!$this->getParent() instanceof ClassDescriptor)) {
            return $this->constants;
        }

        return $this->constants->merge($this->getParent()->getConstants(true));
    }

    /**
     * {@inheritDoc}
     */
    public function setMethods(Collection $methods)
    {
        $this->methods = $methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getInheritedMethods()
    {
        if (!$this->getParent() || (!$this->getParent() instanceof ClassDescriptor)) {
            return new Collection();
        }

        $inheritedMethods = clone $this->getParent()->getMethods();
        $inheritedMethods->merge($this->getParent()->getInheritedMethods());

        return $inheritedMethods;
    }

    /**
     * @return Collection
     */
    public function getMagicMethods()
    {
        /** @var Collection $methodTags */
        $methodTags = clone $this->getTags()->get('method', new Collection());

        if ($this->getParent() instanceof static) {
            $methodTags->merge($this->getParent()->getMagicMethods());
        }

        $methods = new Collection();

        /** @var Tag\MethodDescriptor $methodTag */
        foreach ($methodTags as $methodTag) {
            $method = new MethodDescriptor();
            $method->setName($methodTag->getVariableName());
            $method->setDescription($methodTag->getDescription());

            $methods->add($method);
        }

        return $methods;
    }

    /**
     * {@inheritDoc}
     */
    public function setProperties(Collection $properties)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritDoc}
     */
    public function getInheritedProperties()
    {
        if (!$this->getParent() || (!$this->getParent() instanceof ClassDescriptor)) {
            return new Collection();
        }

        $inheritedProperties = clone $this->getParent()->getProperties();
        $inheritedProperties->merge($this->getParent()->getInheritedProperties());

        return $inheritedProperties;
    }

    /**
     * @return Collection
     */
    public function getMagicProperties()
    {
        /** @var Collection $propertyTags */
        $propertyTags = clone $this->getTags()->get('property', new Collection());
        $propertyTags->merge($this->getTags()->get('property-read', new Collection()));
        $propertyTags->merge($this->getTags()->get('property-write', new Collection()));

        if ($this->getParent() instanceof static) {
            $propertyTags->merge($this->getParent()->getMagicProperties());
        }

        $properties = new Collection();

        /** @var Tag\PropertyDescriptor $propertyTag */
        foreach ($propertyTags as $propertyTag) {
            $property = new PropertyDescriptor();
            $property->setName($propertyTag->getVariableName());
            $property->setDescription($propertyTag->getDescription());
            $property->setTypes($propertyTag->getTypes());

            $properties->add($property);
        }

        if ($this->getParent() instanceof ClassDescriptor) {
            $properties->merge($this->getParent()->getMagicProperties());
        }

        return $properties;
    }

    public function setPackage($package)
    {
        parent::setPackage($package);

        foreach ($this->getConstants() as $constant) {
            $constant->setPackage($package);
        }

        foreach ($this->getProperties() as $property) {
            $property->setPackage($package);
        }

        foreach ($this->getMethods() as $method) {
            $method->setPackage($package);
        }
    }
}
