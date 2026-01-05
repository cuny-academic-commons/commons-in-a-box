# Commons In A Box

[![Build Status](https://travis-ci.org/cuny-academic-commons/commons-in-a-box.svg?branch=1.1.x)](https://travis-ci.org/cuny-academic-commons/commons-in-a-box)

## About The Project

[Commons In A Box](https://commonsinabox.org/) (CBOX) takes the complexity out of creating an online space, or digital commons, for community and collaboration. Built using the [WordPress](https://wordpress.org) publishing platform, with [BuddyPress](https://buddypress.org) for social networking, CBOX comes in two customizable packages:

**CBOX Classic is designed for communities of all kinds.** It is used by groups and organizations to create flexible social networks where members can collaborate on projects, publish research, and create repositories of knowledge.

**CBOX OpenLab is specifically designed for teaching, learning, and collaboration.** It allows faculty members, departments, and entire institutions to create commons spaces for open learning.

Commons In A Box is developed by a team based at [The Graduate Center, CUNY](https://www.gc.cuny.edu). It was originally made possible by a grant from the [Alfred P. Sloan Foundation](https://sloan.org). CBOX OpenLab, a collaboration with [New York City College of Technology](http://www.citytech.cuny.edu), was created with funding from the National Endowment for the Humanities’ [Office of Digital Humanities](https://www.neh.gov/divisions/odh). CBOX receives continuing support from the City University of New York and The Graduate Center, CUNY. 

## Users

Have questions about Commons In A Box? Visit [commonsinabox.org](https://commonsinabox.org), where you'll find robust documentation and support forums.

Think you've found a bug? You may consider posting your issue first in the support forum for [CBOX Classic](http://commonsinabox.org/groups/help-support/forum/) or [CBOX OpenLab](http://commonsinabox.org/groups/help-support/forum/), as appropriate. If you're comfortable using Github, you can also post issues directly to [our issue tracker](https://github.com/cuny-academic-commons/commons-in-a-box/issues).

## Developers

Developers who are interested in contributing to Commons In A Box can send pull requests to this repo. Note that the master branch mirrors *the last stable release*. The current maintenance branch is 1.1.x; please submit requests against this branch.

The current repo contains the core Commons In A Box plugin. Package-specific functionality is handled in a number of related repositories:
- [https://github.com/cuny-academic-commons/cbox-theme](https://github.com/cuny-academic-commons/cbox-theme) is the theme for the Classic package
- [https://github.com/cuny-academic-commons/openlab-theme](https://github.com/cuny-academic-commons/openlab-theme) is the theme for the OpenLab package
- [https://github.com/cuny-academic-commons/cbox-openlab-core](https://github.com/cuny-academic-commons/cbox-openlab-core) is the utility plugin that serves as a bridge between Commons In A Box and the openlab-theme, when running the OpenLab package

Pull requests may be submitted to any of these repos. New issue reports can be opened on the primary commons-in-a-box repository.

If you have a plugin that you would like us to consider for inclusion in CBOX Classic or CBOX OpenLab, see our [plugin submission process](http://commonsinabox.org/technical-guide-development).

Questions or suggestions? Contact us via our [developer support forum](http://commonsinabox.org/groups/cbox-developers/forum/).

## Automated Releases

When a new tag is created in this repository, a GitHub Actions workflow automatically builds a WordPress-ready plugin package. The workflow:

1. Runs the `cbox-distro` script to download GitHub-hosted plugins/themes and update references to local zip files
2. Checks out sister packages (cbox-openlab-core, bp-event-organiser, bp-group-announcements, external-group-blogs) at their pinned versions
3. Extracts translatable strings from sister packages using WP-CLI
4. Generates a `.pot` file with all strings from the main plugin and sister packages
5. Creates a clean zip file named `commons-in-a-box-{version}.zip` with the proper directory structure
6. Creates a GitHub Release and attaches the zip file

The resulting zip file can be uploaded directly to a WordPress installation, just like you would get from the wordpress.org repository.

To trigger a release, simply create and push a new tag:

```bash
git tag 1.7.1
git push origin 1.7.1
```

The workflow will automatically create a GitHub Release with the WP-ready zip file attached.
