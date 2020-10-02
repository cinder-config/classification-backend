<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectFixture extends Fixture
{
    private const PROJECTS = ['geoserver/geoserver', 'liip/LiipFunctionalTestBundle', 'facebook/rocksdb'];
    private const SAMPLE_DESCRIPTION = 'If Monty Python is too childish for you, here is another cool alternative to Lorem Ipsum: Nietzsche Ipsum. The German philosopher from 1800s is still very relevant today. Regardless of how you feel towards his philosophy, there is no denying that his words are elegant and still have impact.';
    private const SAMPLE_CONFIGURATION = <<<'TAG'
cache:
  directories:
    - "$HOME/.m2"
    - downloads
language: java
before_install:
  - rm ~/.m2/settings.xml
  - unset _JAVA_OPTIONS
env:
  global:
    - MAVEN_OPTS=-Xmx512m
    - MAVEN_VERSION=3.6.3
    - TAKARI_SMART_BUILDER_VERSION=0.6.1
before_script:
  - mkdir -p downloads
  - export MAVEN_ZIP=apache-maven-$MAVEN_VERSION-bin.zip
  - |
    if [ ! -f downloads/$MAVEN_ZIP ]; then
      wget -P downloads https://repo.maven.apache.org/maven2/org/apache/maven/apache-maven/$MAVEN_VERSION/$MAVEN_ZIP
    fi
  - export SMART_JAR=takari-smart-builder-$TAKARI_SMART_BUILDER_VERSION.jar
  - |
    if [ ! -f downloads/$SMART_JAR ]; then
      wget -P downloads https://repo1.maven.org/maven2/io/takari/maven/takari-smart-builder/$TAKARI_SMART_BUILDER_VERSION/$SMART_JAR
    fi
  - unzip downloads/$MAVEN_ZIP
  - export M2_HOME=$PWD/apache-maven-$MAVEN_VERSION
  - cp downloads/$SMART_JAR $M2_HOME/lib/ext
  - export PATH=$M2_HOME/bin:$PATH
  - mvn --version
install:
  - if [ "$ACTION" == "docs" ]; then sudo pip install sphinx requests; fi
script:
  - if [ "$ACTION" == "build" ]; then mvn -f src/pom.xml -B -U -T3 -fae -Prelease --builder smart clean install $ARGS && mvn -f src/community/pom.xml -nsu -B -U -T4 -fae -DskipTests -Prelease -PcommunityRelease --builder smart  clean install $COMMUNITY_ARGS; fi
  - if [ "$ACTION" == "build" ]; then grep -H "<testsuite" `find . -iname "TEST-*.xml"` | sed 's/.\/\(.*\)\/target.*:<testsuite .* name="\(.*\)" time="\([^"]*\)" .*/\3\t\2\t\1/' | sort -nr | head -50; fi
  - if [ "$ACTION" == "docs" ]; then mvn -f doc/en install; fi
  - if [ "$ACTION" == "package" ]; then mvn -f src/pom.xml -B -U -T3 -fae --builder smart -Prelease,communityRelease clean install -DskipTests; mvn -f src/pom.xml assembly:single -nsu -N; mvn -f src/community/pom.xml assembly:single -nsu -N; fi
before_cache:
  - rm -rf $HOME/.m2/repository/org/geotools
  - rm -rf $HOME/.m2/repository/org/geowebcache
  - rm -rf $HOME/.m2/repository/org/geoserver
notifications:
  email:
    on_success: never
    on_failure: never
matrix:
  include:
    - jdk: oraclejdk8
      dist: trusty
      env: ACTION=build ARGS="-Dfmt.skip=true" COMMUNITY_ARGS=$ARGS 
      name: Java 8 build
    - jdk: openjdk11
      env: ACTION=build ARGS="-Dfmt.skip=true" COMMUNITY_ARGS=$ARGS
      name: Java 11 build
    - jdk: openjdk11
      env: ACTION=build ARGS="-Dfmt.action=check -Dqa -DskipTests=true" COMMUNITY_ARGS="-Dfmt.action=check -DskipTests=true"
      name: QA build on Java 11
    - jdk: openjdk11
      env: ACTION=docs
      name: Documentation build
    - jdk: openjdk11
      env: ACTION=package
      name: Build release packages
TAG;
    private const SAMPLE_CONFIGURATION_URL = 'https://github.com/geoserver/geoserver/blob/master/.travis.yml';
    private const SAMPLE_GIT_URL = 'https://github.com/geoserver/geoserver';
    private const SAMPLE_TRAVIS_CI_URL = 'https://travis-ci.org/github/geoserver/geoserver';

    public function load(ObjectManager $manager)
    {
        foreach (self::PROJECTS as $name) {
            $project = new Project();
            $project->setName($name);
            $project->setDescription(self::SAMPLE_DESCRIPTION);
            $project->setConfiguration(self::SAMPLE_CONFIGURATION);
            $project->setConfigurationUrl(self::SAMPLE_CONFIGURATION_URL);
            $project->setGitUrl(self::SAMPLE_GIT_URL);
            $project->setTravisCiUrl(self::SAMPLE_TRAVIS_CI_URL);

            $manager->persist($project);
        }

        $manager->flush();
    }
}
