//========================================================================
//
// SplashFTFontFile.h
//
//========================================================================

//========================================================================
//
// Modified under the Poppler project - http://poppler.freedesktop.org
//
// All changes made under the Poppler project to this file are licensed
// under GPL version 2 or later
//
// Copyright (C) 2006 Takashi Iwai <tiwai@suse.de>
// Copyright (C) 2017, 2018 Oliver Sander <oliver.sander@tu-dresden.de>
// Copyright (C) 2017 Adrian Johnson <ajohnson@redneon.com>
// Copyright (C) 2019, 2024 Albert Astals Cid <aacid@kde.org>
// Copyright (C) 2024 g10 Code GmbH, Author: Sune Stolborg Vuorela <sune@vuorela.dk>
//
// To see a description of the changes please see the Changelog file that
// came with your tarball or type make ChangeLog if you are building from git
//
//========================================================================

#ifndef SPLASHFTFONTFILE_H
#define SPLASHFTFONTFILE_H

#include <ft2build.h>
#include FT_FREETYPE_H
#include "SplashFontFile.h"

class SplashFontFileID;
class SplashFTFontEngine;

//------------------------------------------------------------------------
// SplashFTFontFile
//------------------------------------------------------------------------

class SplashFTFontFile : public SplashFontFile
{
public:
    static SplashFontFile *loadType1Font(SplashFTFontEngine *engineA, std::unique_ptr<SplashFontFileID> idA, SplashFontSrc *src, const char **encA, int faceIndexA);
    static SplashFontFile *loadCIDFont(SplashFTFontEngine *engineA, std::unique_ptr<SplashFontFileID> idA, SplashFontSrc *src, int *codeToGIDA, int codeToGIDLenA, int faceIndexA);
    static SplashFontFile *loadTrueTypeFont(SplashFTFontEngine *engineA, std::unique_ptr<SplashFontFileID> idA, SplashFontSrc *src, int *codeToGIDA, int codeToGIDLenA, int faceIndexA);

    ~SplashFTFontFile() override;

    // Create a new SplashFTFont, i.e., a scaled instance of this font
    // file.
    SplashFont *makeFont(SplashCoord *mat, const SplashCoord *textMat) override;

private:
    SplashFTFontFile(SplashFTFontEngine *engineA, std::unique_ptr<SplashFontFileID> idA, SplashFontSrc *src, FT_Face faceA, int *codeToGIDA, int codeToGIDLenA, bool trueTypeA, bool type1A);

    SplashFTFontEngine *engine;
    FT_Face face;
    int *codeToGID;
    int codeToGIDLen;
    bool trueType;
    bool type1;

    friend class SplashFTFont;
};

#endif
