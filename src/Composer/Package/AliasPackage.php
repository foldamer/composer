<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Package;

use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Version\VersionParser;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class AliasPackage extends BasePackage implements CompletePackageInterface
{
    protected $version;
    protected $prettyVersion;
    protected $dev;
    protected $aliasOf;
    protected $rootPackageAlias = false;
    protected $stability;

    protected $requires;
    protected $conflicts;
    protected $provides;
    protected $replaces;
    protected $recommends;
    protected $suggests;

    /**
     * All descendants' constructors should call this parent constructor
     *
     * @param PackageInterface $aliasOf       The package this package is an alias of
     * @param string           $version       The version the alias must report
     * @param string           $prettyVersion The alias's non-normalized version
     */
    public function __construct(PackageInterface $aliasOf, $version, $prettyVersion)
    {
        parent::__construct($aliasOf->getName());

        $this->version = $version;
        $this->prettyVersion = $prettyVersion;
        $this->aliasOf = $aliasOf;
        $this->stability = VersionParser::parseStability($version);
        $this->dev = $this->stability === 'dev';

        // replace self.version dependencies
        foreach (array('requires', 'devRequires') as $type) {
            $links = $aliasOf->{'get'.ucfirst($type)}();
            foreach ($links as $index => $link) {
                // link is self.version, but must be replacing also the replaced version
                if ('self.version' === $link->getPrettyConstraint()) {
                    $links[$index] = new Link($link->getSource(), $link->getTarget(), new VersionConstraint('=', $this->version), $type, $prettyVersion);
                }
            }
            $this->$type = $links;
        }

        // duplicate self.version provides
        foreach (array('conflicts', 'provides', 'replaces') as $type) {
            $links = $aliasOf->{'get'.ucfirst($type)}();
            $newLinks = array();
            foreach ($links as $link) {
                // link is self.version, but must be replacing also the replaced version
                if ('self.version' === $link->getPrettyConstraint()) {
                    $newLinks[] = new Link($link->getSource(), $link->getTarget(), new VersionConstraint('=', $this->version), $type, $prettyVersion);
                }
            }
            $this->$type = array_merge($links, $newLinks);
        }
    }

    public function getAliasOf()
    {
        return $this->aliasOf;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritDoc}
     */
    public function getStability()
    {
        return $this->stability;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrettyVersion()
    {
        return $this->prettyVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function isDev()
    {
        return $this->dev;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * {@inheritDoc}
     */
    public function getConflicts()
    {
        return $this->conflicts;
    }

    /**
     * {@inheritDoc}
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * {@inheritDoc}
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * {@inheritDoc}
     */
    public function getDevRequires()
    {
        return $this->devRequires;
    }

    /**
     * Stores whether this is an alias created by an aliasing in the requirements of the root package or not
     *
     * Use by the policy for sorting manually aliased packages first, see #576
     *
     * @param bool $value
     *
     * @return mixed
     */
    public function setRootPackageAlias($value)
    {
        return $this->rootPackageAlias = $value;
    }

    /**
     * @see setRootPackageAlias
     * @return bool
     */
    public function isRootPackageAlias()
    {
        return $this->rootPackageAlias;
    }

    /***************************************
     * Wrappers around the aliased package *
     ***************************************/

    public function getType()
    {
        return $this->aliasOf->getType();
    }
    public function getTargetDir()
    {
        return $this->aliasOf->getTargetDir();
    }
    public function getExtra()
    {
        return $this->aliasOf->getExtra();
    }
    public function setInstallationSource($type)
    {
        $this->aliasOf->setInstallationSource($type);
    }
    public function getInstallationSource()
    {
        return $this->aliasOf->getInstallationSource();
    }
    public function getSourceType()
    {
        return $this->aliasOf->getSourceType();
    }
    public function getSourceUrl()
    {
        return $this->aliasOf->getSourceUrl();
    }
    public function getSourceReference()
    {
        return $this->aliasOf->getSourceReference();
    }
    public function setSourceReference($reference)
    {
        return $this->aliasOf->setSourceReference($reference);
    }
    public function getDistType()
    {
        return $this->aliasOf->getDistType();
    }
    public function getDistUrl()
    {
        return $this->aliasOf->getDistUrl();
    }
    public function getDistReference()
    {
        return $this->aliasOf->getDistReference();
    }
    public function getDistSha1Checksum()
    {
        return $this->aliasOf->getDistSha1Checksum();
    }
    public function getScripts()
    {
        return $this->aliasOf->getScripts();
    }
    public function getLicense()
    {
        return $this->aliasOf->getLicense();
    }
    public function getAutoload()
    {
        return $this->aliasOf->getAutoload();
    }
    public function getDevAutoload()
    {
        return $this->aliasOf->getDevAutoload();
    }
    public function getIncludePaths()
    {
        return $this->aliasOf->getIncludePaths();
    }
    public function getRepositories()
    {
        return $this->aliasOf->getRepositories();
    }
    public function getReleaseDate()
    {
        return $this->aliasOf->getReleaseDate();
    }
    public function getBinaries()
    {
        return $this->aliasOf->getBinaries();
    }
    public function getKeywords()
    {
        return $this->aliasOf->getKeywords();
    }
    public function getDescription()
    {
        return $this->aliasOf->getDescription();
    }
    public function getHomepage()
    {
        return $this->aliasOf->getHomepage();
    }
    public function getSuggests()
    {
        return $this->aliasOf->getSuggests();
    }
    public function getAuthors()
    {
        return $this->aliasOf->getAuthors();
    }
    public function getSupport()
    {
        return $this->aliasOf->getSupport();
    }
    public function getNotificationUrl()
    {
        return $this->aliasOf->getNotificationUrl();
    }
    public function getArchiveExcludes()
    {
        return $this->aliasOf->getArchiveExcludes();
    }
    public function __toString()
    {
        return parent::__toString().' (alias of '.$this->aliasOf->getVersion().')';
    }
}
