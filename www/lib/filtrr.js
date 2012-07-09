/**
 * filtrr.js - Javascript Image Processing Library
 *
 * Copyright (C) 2011 Alexandros Michael
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

 /* The filtr object contains all the image processing functions,
  * and it is returned as a parameter to the callback passed in filtrr.
  */
 var filtr = function(_canvas) {

    if (!_canvas) {
        throw "Canvas supplied to filtr was null or undefined.";
    }

    var canvas    = _canvas;
    var w         = canvas.width;
    var h         = canvas.height;
    var ctx       = canvas.getContext("2d");
    var imageData = ctx.getImageData(0, 0, w, h);

    /**
     * Clamps the intensity level between 0 - 255.
     *
     * @param i The intensity level.
     */
    var safe = function(i)
    {
        return Math.min(255, Math.max(0, i));
    };

    /**
     * Returns a new copy of this filtr. Useful when
     * wanting to blend multiple layers of the image.
     */
    this.duplicate = function()
    {
        return new filtr(canvas);
    };

    /**
     * Puts the image data in the image. This function is separated from
     * the filters so that many copies of this object can be
     * created and filtered without affecting the underlying image. It
     * is left to the discretion of the programmer when to "put back" the
     * filtered object.
     */
    this.put = function()
    {
        ctx.putImageData(imageData, 0, 0);
    };

    /**
     * Return a reference to the underlying canvas element.
     */
    this.canvas = function()
    {
        return canvas;
    };

    /**
     * Return the imageData array in it's current state.  It is different
     * than getting the imageData from the canvas object because they
     * might not be drawn on the canvas yet.
     */
    this.getCurrentImageData = function()
    {
        return imageData;
    };

    /*
     * The core image processing functions.
     */
    this.core = {

        /**
         * Computes the new pixel values based on the filter function. Can be used
         * to apply custom image processing functions on the image by giving a
         * reference to a pixel-manipulating function.
         *
         * @param fn The function which will compute the new RGB values given the
         *           current RGB values.
         */
        apply : function(fn)
        {
            var data = imageData.data;
            var i = 0, j = 0;
            for (i = 0; i < h; i++) {
                for (j = 0; j < w; j++) {
                    var index = (i*w*4) + (j*4);
                    var rgb = fn(
                        data[index],
                        data[index + 1],
                        data[index + 2],
                        data[index + 3]
                    );
                    data[index]     = rgb.r;
                    data[index + 1] = rgb.g;
                    data[index + 2] = rgb.b;
                    data[index + 3] = rgb.a;
                }
            }
            return this;
        },

        /**
         * Performs a convolution given a kernel.
         *
         * @param kernel The convolution kernel.
         */
        convolve : function(kernel)
        {
            if (!kernel) {
                throw "Kernel was null in convolve function.";
            } else if (kernel.length === 0) {
                throw "Kernel length was 0 in convolve function.";
            }
            var inData = imageData;
            if (!ctx.createImageData) {
                throw "createImageData is not supported."
            }
            var outData   = ctx.createImageData(inData.width, inData.height);
            var outDArray = outData.data;
            var inDArray  = imageData.data;
            var kh = parseInt(kernel.length / 2);
            var kw = parseInt(kernel[0].length / 2);
            var i = 0, j = 0, n = 0, m = 0;
            for (i = 0; i < h; i++) {
                for (j = 0; j < w; j++) {
                    var outIndex = (i*w*4) + (j*4);
                    var r = 0, g = 0, b = 0;
                    for (n = -kh; n <= kh; n++) {
                        for (m = -kw; m <= kw; m++) {
                            if (i + n >= 0 && i + n < h) {
                                if (j + m >= 0 && j + m < w) {
                                    var f = kernel[n + kh][m + kw];
                                    if (f === 0) {continue;}
                                    var inIndex = ((i+n)*w*4) + ((j+m)*4);
                                    r += inDArray[inIndex] * f;
                                    g += inDArray[inIndex + 1] * f;
                                    b += inDArray[inIndex + 2] * f;
                                }
                            }
                        }
                    }
                    outDArray[outIndex]     = safe(r);
                    outDArray[outIndex + 1] = safe(g);
                    outDArray[outIndex + 2] = safe(b);
                    outDArray[outIndex + 3] = 255;
                }
            }
            return outData;
        },

        /**
         * Edge detection. Three possible methods are offered - Simple (a simple horizontal edge detection),
         * Sobel and Canny.
         *
         * @param type The type of edge detector - possible values "simple", "sobel", "canny".
         */
        edgeDetection : function(type) {
            var inData  = imageData;
            var inDArray = imageData.data;
            var i = 0, j = 0, index = 0;
            if (type.toLowerCase() === "simple") {
                if (!ctx.createImageData) {
                    throw "createImageData is not supported.";
                }
                var outData = ctx.createImageData(inData.width, inData.height);
                var outDArray = outData.data;
                for (i = 0; i < h; i++) {
                    for (j = 1; j < w; j++) {
                        index = (i*w*4) + (j*4);
                        var leftIndex = (i*w*4) + ((j-1)*4);
                        outDArray[index]     = safe(Math.abs(inDArray[index]     - inDArray[leftIndex]));
                        outDArray[index + 1] = safe(Math.abs(inDArray[index + 1] - inDArray[leftIndex + 1]));
                        outDArray[index + 2] = safe(Math.abs(inDArray[index + 2] - inDArray[leftIndex + 2]));
                        outDArray[index + 3] = 255;
                    }
                }
                imageData = outData;

            } else if (type.toLowerCase() === "sobel") {

                var gH = this.convolve([
                    [-1.0, -2.0, -1.0],
                    [0.0,   0.0,  0.0],
                    [1.0,   2.0,  1.0]
                ]);
                var gV = this.convolve([
                    [-1.0, 0.0, 1.0],
                    [-2.0, 0.0, 2.0],
                    [-1.0, 0.0, 1.0]
                ]);
                var gHArray = gH.data;
                var gVArray = gV.data;
                for (i = 0; i < h; i++) {
                    for (j = 0; j < w; j++) {
                        index = (i*w*4) + (j*4);
                        var rH  = gHArray[index],
                            rV  = gVArray[index],
                            grH = gHArray[index + 1],
                            grV = gVArray[index + 1],
                            bH  = gHArray[index + 2],
                            bV  = gVArray[index + 2];
                        inDArray[index]     = Math.sqrt(rH * rH + rV * rV);
                        inDArray[index + 1] = Math.sqrt(grH * grH + grV * grV);
                        inDArray[index + 2] = Math.sqrt(bH * bH + bV * bV);
                    }
                }
                imageData = inData;

            } else if (type.toLowerCase() === "canny") {
                // Not implemented yet.
            }
            return this;
        },

        /**
         * Adjusts the RGB values by a given factor.
         *
         * @param rS Red channel factor.
         * @param gS Green channel factor.
         * @param bS Blue channel factor.
         */
        adjust : function(rS, gS, bS)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(r * (1 + rS)),
                    g: safe(g * (1 + gS)),
                    b: safe(b * (1 + bS)),
                    a: a
                };
            });
            rS = gS = bS = null;
            return this;
        },

        /**
         * Adjusts the brightness by a given factor.
         *
         * @param t The factor to adjust the brightness by.
         */
        brightness : function(t)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(r + t),
                    g: safe(g + t),
                    b: safe(b + t),
                    a: a
                };
            });
            t = null;
            return this;
        },

        /**
         * Fill the image with a color.
         *
         * @param rF Red channel.
         * @param gF Green channel.
         * @param bF Blue channel.
         */
        fill : function(rF, gF, bF)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(rF),
                    g: safe(gF),
                    b: safe(bF),
                    a: a
                };
            });
            rf = gF = bF = null;
            return this;
        },

        /**
         * Multiply the opacity by a given factor.
         *
         * @param o The factor to multply the opacity by.
         */
        opacity : function(o)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: r,
                    g: g,
                    b: b,
                    a: safe(o * a)
                };
            });
            o = null;
            return this;
        },

        /**
         * Adjusts the saturation by a given factor.
         *
         * @param t The factor to adjust the saturation by.
         */
        saturation : function(t)
        {
            this.apply(function(r, g, b, a)
            {
                var avg = ( r + g + b ) / 3;
                return {
                    r: safe(avg + t * (r - avg)),
                    g: safe(avg + t * (g - avg)),
                    b: safe(avg + t * (b - avg)),
                    a: a
                };
            });
            t = null;
            return this;
        },

        /**
         * Uses a threshold number on each channel - intensities below
         * the threshold are turned black and intensities above are turned white.
         *
         * @param t The threshold.
         */
        threshold : function(t)
        {
            this.apply(function(r, g, b, a)
            {
                var c = 255;
                if (r < t|| g < t || b < t) {
                    c = 0;
                }
                return {
                    r: c,
                    g: c,
                    b: c,
                    a: a
                };
            });
            t = null;
            return this;
        },

        /**
         * Quantizes the colors in the image like a posterization effect.
         *
         * @param levels The levels of quantization.
         */
        posterize : function(levels)
        {
            var step = Math.floor(255 / levels);
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(Math.floor(r / step) * step),
                    g: safe(Math.floor(g / step) * step),
                    b: safe(Math.floor(b / step) * step),
                    a: a
                };
            });
            step = null;
            levels = null;
            return this;
        },

        /**
         * Changes the gamma of the image.
         *
         * @param value The gamma value.
         */
        gamma : function(value)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(Math.pow(r, value)),
                    g: safe(Math.pow(g, value)),
                    b: safe(Math.pow(b, value)),
                    a: a
                };

            });
            value = null;
            return this;
        },

        /**
         * Inverts the colors in the image.
         */
        negative: function()
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(255 - r),
                    g: safe(255 - g),
                    b: safe(255 - b),
                    a: a
                };
            });
            return this;
        },

        /**
         * Creates a grayscale version of the image.
         */
        grayScale : function()
        {
            this.apply(function(r, g, b, a)
            {
                var avg = (r + g + b) / 3;
                return {
                    r: safe(avg),
                    g: safe(avg),
                    b: safe(avg),
                    a: a
                };
            });
            return this;
        },

        /**
         * Embosses the edges of the image.
         */
        bump : function()
        {
            imageData = this.convolve([
                [-1.0, -1.0,  0.0],
                [-1.0,  1.0,  1.0],
                [ 0.0,  1.0,  1.0]
            ]);
            return this;
        },

        /**
         * Interpolates between the given RGB values. Changes the tint of the image.
         *
         * @param maxRGB The maximum RGB values.
         * @param minRGB The minimum RGB values.
         */
        tint : function(minRGB, maxRGB)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe((r - minRGB[0]) * ((255 / (maxRGB[0] - minRGB[0])))),
                    g: safe((g - minRGB[1]) * ((255 / (maxRGB[1] - minRGB[1])))),
                    b: safe((b - minRGB[2]) * ((255 / (maxRGB[2] - minRGB[2])))),
                    a: a
                };
            });
            minRGB = maxRGB = null;
            return this;
        },

        /**
         * Applies a mask on each channel.
         *
         * @param mR Red channel mask.
         * @param mG Green channel mask.
         * @param mB Blue channel mask.
         */
        mask : function(mR, mG, mB)
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(r & mR),
                    g: safe(g & mG),
                    b: safe(b & mB),
                    a: a
                };
            });
            mR = mG = mB = null;
            return this;
        },

        /**
         * Applies a sepia filter.
         */
        sepia : function()
        {
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe((r * 0.393) + (g * 0.769) + (b * 0.189)),
                    g: safe((r * 0.349) + (g * 0.686) + (b * 0.168)),
                    b: safe((r * 0.272) + (g * 0.534) + (b * 0.131)),
                    a: a
                };
            });
            return this;
        },

        /**
         * Make color lighter or darker by a given factor.
         *
         * @param t The factor to adjust the bias by.
         */
        bias : function(val)
        {
            function calc(f, bi){
                return f / ((1.0 / bi - 1.9) * (0.9 - f) + 1);
            }
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(r * calc(r / 255, val)),
                    g: safe(g * calc(g / 255, val)),
                    b: safe(b * calc(b / 255, val)),
                    a: a
                };
            });
            val = null;
            return this;
        },

        /**
         * Adjusts the contrast by a given factor.
         *
         * @param t The factor to adjust the contrast by.
         */
        contrast : function(val)
        {
            function calc(f, c){
                return (f-0.5) * c + 0.5;
            }
            this.apply(function(r, g, b, a)
            {
                return {
                    r: safe(255 * calc(r / 255, val)),
                    g: safe(255 * calc(g / 255, val)),
                    b: safe(255 * calc(b / 255, val)),
                    a: a
                };
            });
            val = null;
            return this;
        },

        /**
         * A simple convolution blur.
         */
        blur: function()
        {
            imageData = this.convolve([
                [1, 2, 1],
                [2, 2, 2],
                [1, 2, 1]
            ]);
            return this;
        },

        /**
         * Convolution sharpening.
         */
        sharpen : function()
        {
            imageData = this.convolve([
                [0.0, -0.2,  0.0],
                [-0.2, 1.8, -0.2],
                [0.0, -0.2,  0.0]
            ]);
            return this;
        },

        /**
         * Gaussian blur with a 5x5 convolution kernel.
         */
        gaussianBlur: function()
        {
            imageData = this.convolve([
                [1/273, 4/273, 7/273, 4/273, 1/273],
                [4/273, 16/273, 26/273, 16/273, 4/273],
                [7/273, 26/273, 41/273, 26/273, 7/273],
                [4/273, 16/273, 26/273, 16/273, 4/273],
                [1/273, 4/273, 7/273, 4/273, 1/273]
            ]);
            return this;
        }
    };

    /* Blending modes. Each mode takes another filtr object representing the layer to be blended on top. */
    this.blend = {

        apply : function(topFiltr, fn)
        {
            var blendData = topFiltr.getCurrentImageData();
            var blendDArray = blendData.data;
            var imageDArray = imageData.data;
            var i = 0, j = 0;
            for (i = 0; i < h; i++) {
                for (j = 0; j < w; j++) {
                    var index = (i*w*4) + (j*4);
                    var rgba = fn(
                        {r: blendDArray[index],
                         g: blendDArray[index + 1],
                         b: blendDArray[index + 2],
                         a: blendDArray[index + 3]},
                         {r: imageDArray[index],
                          g: imageDArray[index + 1],
                          b: imageDArray[index + 2],
                          a: imageDArray[index + 3]}
                    );
                    imageDArray[index] = rgba.r;
                    imageDArray[index + 1] = rgba.g;
                    imageDArray[index + 2] = rgba.b;
                    imageDArray[index + 3] = rgba.a;
                }
            }
        },

        /**
         * Multiply blend mode.
         */
        multiply: function(topFiltr)
        {
            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe((top.r * bottom.r) / 255),
                    g: safe((top.g * bottom.g) / 255),
                    b: safe((top.b * bottom.b) / 255),
                    a: bottom.a
                };
            });
            return this;
        },

        /**
         * Screen blend mode.
         */
        screen : function(topFiltr)
        {
            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(255 - (((255 - top.r) * (255 - bottom.r)) / 255)),
                    g: safe(255 - (((255 - top.g) * (255 - bottom.g)) / 255)),
                    b: safe(255 - (((255 - top.b) * (255 - bottom.b)) / 255)),
                    a: bottom.a
                };
            });
            return this;
        },

        /**
         * Overaly blend mode - a combination of multiply and screen.
         */
        overlay : function(topFiltr)
        {
            function calc(b, t) {
                return (b > 128) ? 255 - 2 * (255 - t) * (255 - b) / 255: (b * t * 2) / 255;
            }

            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(calc(bottom.r, top.r)),
                    g: safe(calc(bottom.g, top.g)),
                    b: safe(calc(bottom.b, top.b)),
                    a: bottom.a
                };
            });
            return this;
        },

        /**
         * Difference blend mode - subtracts bottom from top.
         */
        difference : function(topFiltr)
        {
            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(Math.abs(top.r - bottom.r)),
                    g: safe(Math.abs(top.g - bottom.g)),
                    b: safe(Math.abs(top.b - bottom.b)),
                    a: bottom.a
                };
            });
            return this;
        },

        /**
         * Addition blend mode - adds top to bottom.
         */
        addition : function(topFiltr)
        {
            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(top.r + bottom.r),
                    g: safe(top.g + bottom.g),
                    b: safe(top.b + bottom.b),
                    a: bottom.a
                };
            });
            return this;
        },

        /**
         * Exclusion blend mode - similar to difference with lower contrast.
         */
        exclusion : function(topFiltr)
        {
            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(128 - 2 * (bottom.r - 128) * (top.r - 128) / 255),
                    g: safe(128 - 2 * (bottom.g - 128) * (top.g - 128) / 255),
                    b: safe(128 - 2 * (bottom.b - 128) * (top.b - 128) / 255),
                    a: bottom.a

                };
            });
            return this;
        },

        /**
         * Soft light blend mode - a softer version of Overlay.
         */
        softLight : function(topFiltr)
        {
            function calc(b, t) {
                return (b > 128) ? 255 - ((255 - b) * (255 - (t - 128))) / 255 : (b * (t + 128)) / 255;
            }

            this.apply(topFiltr, function(top, bottom)
            {
                return {
                    r: safe(calc(bottom.r, top.r)),
                    g: safe(calc(bottom.g, top.g)),
                    b: safe(calc(bottom.b, top.b)),
                    a: bottom.a
                };
            });
            return this;
        }
    };
 };

 /* filtrr is a singleton class.
  * It can be attached directly to an <img> element or a <canvas> element.
  */
 var filtrr = new function() {

    /**
     * Is it a string?
     *
     * @param srt The variable to be checked.
     **/
    var isString = function(str)
    {
        return (typeof str === "string")
               || (!isNaN(str))
               || (str.substring);
    };

    /**
     * Find the position of the object in the document.
     *
     * @param obj The object in question.
     */
    var findPos = function(obj)
    {
        var curleft = 0;
        var curtop  = 0;
        if (obj.offsetParent) {
            while(true) {
                curtop += obj.offsetTop;
                curleft += obj.offsetLeft;
                if(!obj.offsetParent) { break; }
                obj = obj.offsetParent;
            }
        } else {
            if (obj.x) {
                curleft += obj.x;
            }
            if (obj.y) {
                curtop += obj.y;
            }
        }
        return {top: curtop, left: curleft};
    }

    /**
     * Replaces an image element with a canvas.
     *
     * @param elemOrId The id of the image element or the actual DOM object.
     * @callback The callback function to be executed once the image
     *           has be loaded.
     **/
    this.filter_and_cover_img = function(imgId, imgSrc, callback)
    {
        var imgElem = document.getElementById(imgId);
        if (imgElem) {

            var img = new Image();
            img.onload = function()
            {
		$("#"+imgId).parent().parent().find("canvas").remove();

                var canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;

		var w = $("#"+imgId).width();
		var h = $("#"+imgId).height();

		canvas.getContext("2d").scale(w/canvas.width,h/canvas.height);
                canvas.getContext("2d").drawImage(img, 0, 0);
                var pos = findPos(imgElem);
                var posP = findPos(imgElem.offsetParent);
                canvas.style.top = Math.abs(pos.top - posP.top) + "px";
                canvas.style.left = Math.abs(pos.left - posP.left) + "px";
                canvas.style.position = "absolute";


                if (imgElem.offsetParent) {
		    imgElem.offsetParent.insertBefore(canvas, imgElem.offsetParent.firstChild);
                }
                callback(new filtr(canvas));
            };
            img.src = imgSrc;
        } else {
            throw "Could not find image element with id: " + id;
        }
    };



    this.filter_and_save = function(imgID, imgSrc, callback)
    {
        var img = new Image();
        img.onload = function()
        {
	    var canvas = document.createElement("canvas");

            canvas.width = img.width;
            canvas.height = img.height;
	    //alert(imgID);
   
            canvas.getContext("2d").drawImage(img, 0, 0);
            callback(new filtr(canvas));

	    var image = canvas.toDataURL("image/png");
	    image = image.replace('data:image/png;base64,', '');

	    $.ajax({
		type: 'POST',
		url: '/save_filtered.php',
		sync: true, 
		data: { 'imageData': image, 'imageSrc': imgSrc },
		cache: false,
		success: function (msg) {
		}
	    });
        };
        img.src = imgSrc;
    };

    /**
     * Creates a filtr object from a canvas.
     *
     * @param elemOrId The id of the canvas element or the actual DOM object.
     * @callback The callback function to be executed once the canvas
     *           has been loaded - just to be consistent with the image async loading.
     **/
    this.canvas = function(elemOrId, callback)
    {
        var canvasElem = (isString(elemOrId))? document.getElementById(elemOrId) : elemOrId;
        if (canvasElem) {
            callback(new filtr(canvasElem));
        } else {
            throw "Could not find element with id: " + id;
        }
    };
 };









/* Predefine our effects as functions for easy calling. */
var RUN_EFFECT = {

    e1 : function(imageID, imageSrc) {

        /* This is the starting point to apply filtrr on your images.
         * Using the img() function you can pass in an id or the img element,
         * and a callback function, to be called when the image has been loaded.
         * The callback function is given a filtr parameter which is a wrapper around
         * the canvas element, and contains all the filter and blending methods.
         */
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {

            /* filtr.duplicate() can be used to get a duplicate of the filtr object
             * so you can blend many together.*/
            var topFiltr = filtr.duplicate();

            /* filtr.core contains all the core filters. Filter calls can be chained.
             * filtr.blend contains all the blending modes. */
            topFiltr.core.saturation(0).blur();

            /* Here we are blending the topFiltr on top of the filtr object. */
            filtr.blend.multiply(topFiltr);

            /* All filters after a blending, will apply on the blended filtr object */
            filtr.core.tint([60, 35, 10], [170, 140, 160]).contrast(0.8).brightness(10);

            /* You need to call put() on a filtr object to see your filter applied on
             * the image. This is explained in the commented version of filtrr.js */
            filtr.put();

            /* filtr also gives a reference to the underlying canvas object using filtr.canvas().
             * This is very useful in drawing frames or other images on top - so for example you
             * can draw the white frame as in my example, or a wrinkly pattern.
             */
            //filtr.canvas().getContext("2d").drawImage(whiteFrame, 0, 0);

        });
    },

    e2 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.saturation(0.3).posterize(70).tint([50, 35, 10], [190, 190, 230]);
            filtr.put();
        });
    },

    e3 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.tint([60, 35, 10], [170, 170, 230]).contrast(0.8);
            filtr.put();
        });
    },

    e4 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.grayScale().tint([60,60,30], [210, 210, 210]);
            filtr.put();
	    filtr.canvas().getContext("2d").scale(0.5,0.5);
        });
    },

    e5 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.tint([30, 40, 30], [120, 170, 210])
                      .contrast(0.75)
                      .bias(1)
                      .saturation(0.6)
                      .brightness(20);
            filtr.put();
        });
    },

    e6 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.saturation(0.4).contrast(0.75).tint([20, 35, 10], [150, 160, 230]);
            filtr.put();
        });
    },

    e7 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            var topFiltr = filtr.duplicate();
            topFiltr.core.tint([20, 35, 10], [150, 160, 230]).saturation(0.6);
            filtr.core.adjust(0.1,0.7,0.4).saturation(0.6).contrast(0.8);
            filtr.blend.multiply(topFiltr);
            filtr.put();
        });
    },

    e8 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            /* In this example we are creating 3 different duplicate layers. Each one is filtered
             * and then blended on the filtr object. Note that you could say blend topFiltr1 and
             * topFiltr2 together and then blend the resuln on filtr.
             */
            var topFiltr = filtr.duplicate();
            var topFiltr1 = filtr.duplicate();
            var topFiltr2 = filtr.duplicate();
            topFiltr2.core.fill(167, 118, 12);
            topFiltr1.core.gaussianBlur();
            topFiltr.core.saturation(0);
            filtr.blend.overlay(topFiltr);
            filtr.blend.softLight(topFiltr1);
            filtr.blend.softLight(topFiltr2);
            filtr.core.saturation(0.5).contrast(0.86);
            filtr.put();
        });
    },

    e9 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            var topFiltr = filtr.duplicate();
            var topFiltr1 = filtr.duplicate();
            topFiltr1.core.fill(226, 217, 113).saturation(0.2);
            topFiltr.core.gaussianBlur().saturation(0.2);
            topFiltr.blend.multiply(topFiltr1);
            filtr.core.saturation(0.2).tint([30, 45, 40], [110, 190, 110]);
            filtr.blend.multiply(topFiltr);
            filtr.core.brightness(20).sharpen().contrast(1.1);
            filtr.put();
        });
    },

    e10 : function(imageID, imageSrc) {
        filtrr.filter_and_cover_img(imageID, imageSrc, function(filtr) {
            filtr.core.sepia().bias(0.6);
            filtr.put();
        });
    }
};







/* Run effect and then run ajax call to send filtered image to backend */
var SAVE_EFFECT = {

    e1 : function(imageID, imageSrc) {

        /* This is the starting point to apply filtrr on your images.
         * Using the img() function you can pass in an id or the img element,
         * and a callback function, to be called when the image has been loaded.
         * The callback function is given a filtr parameter which is a wrapper around
         * the canvas element, and contains all the filter and blending methods.
         */
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {

            /* filtr.duplicate() can be used to get a duplicate of the filtr object
             * so you can blend many together.*/
            var topFiltr = filtr.duplicate();

            /* filtr.core contains all the core filters. Filter calls can be chained.
             * filtr.blend contains all the blending modes. */
            topFiltr.core.saturation(0).blur();

            /* Here we are blending the topFiltr on top of the filtr object. */
            filtr.blend.multiply(topFiltr);

            /* All filters after a blending, will apply on the blended filtr object */
            filtr.core.tint([60, 35, 10], [170, 140, 160]).contrast(0.8).brightness(10);

            /* You need to call put() on a filtr object to see your filter applied on
             * the image. This is explained in the commented version of filtrr.js */
            filtr.put();

            /* filtr also gives a reference to the underlying canvas object using filtr.canvas().
             * This is very useful in drawing frames or other images on top - so for example you
             * can draw the white frame as in my example, or a wrinkly pattern.
             */
            //filtr.canvas().getContext("2d").drawImage(whiteFrame, 0, 0);

        });
    },

    e2 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.saturation(0.3).posterize(70).tint([50, 35, 10], [190, 190, 230]);
            filtr.put();
        });
    },

    e3 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.tint([60, 35, 10], [170, 170, 230]).contrast(0.8);
            filtr.put();
        });
    },

    e4 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.grayScale().tint([60,60,30], [210, 210, 210]);
            filtr.put();
	    filtr.canvas().getContext("2d").scale(0.5,0.5);
        });
    },

    e5 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.tint([30, 40, 30], [120, 170, 210])
                      .contrast(0.75)
                      .bias(1)
                      .saturation(0.6)
                      .brightness(20);
            filtr.put();
        });
    },

    e6 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.saturation(0.4).contrast(0.75).tint([20, 35, 10], [150, 160, 230]);
            filtr.put();
        });
    },

    e7 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            var topFiltr = filtr.duplicate();
            topFiltr.core.tint([20, 35, 10], [150, 160, 230]).saturation(0.6);
            filtr.core.adjust(0.1,0.7,0.4).saturation(0.6).contrast(0.8);
            filtr.blend.multiply(topFiltr);
            filtr.put();
        });
    },

    e8 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            /* In this example we are creating 3 different duplicate layers. Each one is filtered
             * and then blended on the filtr object. Note that you could say blend topFiltr1 and
             * topFiltr2 together and then blend the resuln on filtr.
             */
            var topFiltr = filtr.duplicate();
            var topFiltr1 = filtr.duplicate();
            var topFiltr2 = filtr.duplicate();
            topFiltr2.core.fill(167, 118, 12);
            topFiltr1.core.gaussianBlur();
            topFiltr.core.saturation(0);
            filtr.blend.overlay(topFiltr);
            filtr.blend.softLight(topFiltr1);
            filtr.blend.softLight(topFiltr2);
            filtr.core.saturation(0.5).contrast(0.86);
            filtr.put();
        });
    },

    e9 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            var topFiltr = filtr.duplicate();
            var topFiltr1 = filtr.duplicate();
            topFiltr1.core.fill(226, 217, 113).saturation(0.2);
            topFiltr.core.gaussianBlur().saturation(0.2);
            topFiltr.blend.multiply(topFiltr1);
            filtr.core.saturation(0.2).tint([30, 45, 40], [110, 190, 110]);
            filtr.blend.multiply(topFiltr);
            filtr.core.brightness(20).sharpen().contrast(1.1);
            filtr.put();
        });
    },

    e10 : function(imageID, imageSrc) {
        filtrr.filter_and_save(imageID, imageSrc, function(filtr) {
            filtr.core.sepia().bias(0.6);
            filtr.put();
        });
    }
};
