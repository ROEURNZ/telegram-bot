<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PHP Telegram Bot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        #mindmap {
            display: block;
            width: 100vw;
            height: 100vh;
        }

        /* Animation styles */
        .markmap text {
            opacity: 0; /* Initially hidden */
            transform: translateY(-10px); /* Move up slightly */
            transition: opacity 0.3s ease, transform 0.3s ease; /* Transition properties */
        }

        .markmap text.visible {
            opacity: 1; /* Fully visible */
            transform: translateY(0); /* Return to original position */
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/markmap-toolbar@0.17.2/dist/style.css">
</head>

<body>
    <svg id="mindmap"></svg>
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markmap-view@0.17.2/dist/browser/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markmap-toolbar@0.17.2/dist/index.js"></script>
    <script>
        (r => {
            setTimeout(r);
        })(() => {
            const { markmap, mm } = window;
            const { el } = markmap.Toolbar.create(mm);
            el.setAttribute('style', 'position:absolute;bottom:20px;right:20px');
            document.body.append(el);
        });
    </script>
    <script>
        ((getMarkmap, getOptions, root2, jsonOptions) => {
            const markmap = getMarkmap();
            const mapInstance = markmap.Markmap.create(
                "svg#mindmap",
                (getOptions || markmap.deriveOptions)(jsonOptions),
                root2
            );

            // Apply animation
            const texts = d3.selectAll('.markmap text');
            texts.each(function() {
                const text = d3.select(this);
                text.classed('visible', true); // Add class to trigger animation
            });
        })(
            () => window.markmap,
            null,
            {
                "content": "Proposal for PHP Telegram Bot",
                "children": [
                    {
                        "content": "Commands and Button Processes",
                        "children": [
                            {
                                "content": "1. Start Button",
                                "children": [
                                    {
                                        "content": "<strong>Process for Starting the Bot:</strong>",
                                        "children": [
                                            {
                                                "content": "Users must click the <strong>\"Start\"</strong> button to initiate interaction with the bot.",
                                                "children": [],
                                                "payload": { "lines": "10,11" }
                                            },
                                            {
                                                "content": "Users cannot skip the start process; if they do not click \"Start,\" they will be restricted from accessing subsequent steps.",
                                                "children": [],
                                                "payload": { "lines": "11,13" }
                                            }
                                        ],
                                        "payload": { "lines": "9,13" }
                                    }
                                ],
                                "payload": { "lines": "8,9" }
                            },
                            {
                                "content": "2. Language Selection",
                                "children": [
                                    {
                                        "content": "<strong>Process for Language Selection:</strong>",
                                        "children": [
                                            {
                                                "content": "Users can choose their preferred language through the buttons <strong>\"US English\"</strong> and <strong>\"KH ភាសាខ្មែរ\"</strong>.",
                                                "children": [],
                                                "payload": { "lines": "15,16" }
                                            },
                                            {
                                                "content": "This step is only accessible after the user has clicked the <strong>\"Start\"</strong> button in the previous step.",
                                                "children": [],
                                                "payload": { "lines": "16,17" }
                                            },
                                            {
                                                "content": "The same restriction applies: users must complete the previous step to proceed.",
                                                "children": [],
                                                "payload": { "lines": "17,19" }
                                            }
                                        ],
                                        "payload": { "lines": "14,19" }
                                    }
                                ],
                                "payload": { "lines": "13,14" }
                            },
                            {
                                "content": "3. Share Contact",
                                "children": [
                                    {
                                        "content": "<strong>Process for Sharing Contact:</strong>",
                                        "children": [
                                            {
                                                "content": "This step follows the language selection and maintains the restriction from previous steps.",
                                                "children": [],
                                                "payload": { "lines": "21,22" }
                                            },
                                            {
                                                "content": "Users can share their contact information using the <strong>\"Share Contact\"</strong> button (with dynamic labeling based on the selected language) or by using the command <strong>/share_contact</strong>.",
                                                "children": [],
                                                "payload": { "lines": "22,23" }
                                            },
                                            {
                                                "content": "Clicking <strong>/share_contact</strong> sends the user's contact directly to the Telegram bot.",
                                                "children": [],
                                                "payload": { "lines": "23,25" }
                                            }
                                        ],
                                        "payload": { "lines": "20,25" }
                                    }
                                ],
                                "payload": { "lines": "19,20" }
                            },
                            {
                                "content": "4. Upload Barcode or QR Code Images",
                                "children": [
                                    {
                                        "content": "<strong>Process for Uploading Images:</strong>",
                                        "children": [
                                            {
                                                "content": "Users can upload one or more images of barcodes or QR codes (uploading QR codes is optional).",
                                                "children": [],
                                                "payload": { "lines": "27,28" }
                                            },
                                            {
                                                "content": "Access to this step is contingent upon successfully completing previous steps; restrictions apply.",
                                                "children": [],
                                                "payload": { "lines": "28,29" }
                                            },
                                            {
                                                "content": "When users reach this step, the bot will send the message:",
                                                "children": [
                                                    {
                                                        "content": "<strong>upload_prompt</strong>: \"Please upload images containing barcodes or QR codes.\"",
                                                        "children": [],
                                                        "payload": { "lines": "30,31" }
                                                    }
                                                ],
                                                "payload": { "lines": "29,31" }
                                            },
                                            {
                                                "content": "While images are being uploaded, the bot will send:",
                                                "children": [
                                                    {
                                                        "content": "<strong>decode_prompt</strong>: \"Please wait for a few seconds; the bot is processing the images.\"",
                                                        "children": [],
                                                        "payload": { "lines": "32,33" }
                                                    }
                                                ],
                                                "payload": { "lines": "31,33" }
                                            },
                                            {
                                                "content": "Users can either use the <strong>/decode</strong> command or click the <strong>\"Decoding\"</strong> button to initiate decoding.",
                                                "children": [],
                                                "payload": { "lines": "33,34" }
                                            },
                                            {
                                                "content": "Decoded results will be sent to the Telegram bot immediately after processing.",
                                                "children": [],
                                                "payload": { "lines": "34,36" }
                                            }
                                        ],
                                        "payload": { "lines": "26,36" }
                                    }
                                ],
                                "payload": { "lines": "25,26" }
                            },
                            {
                                "content": "5. Share Current Location",
                                "children": [
                                    {
                                        "content": "<strong>Process for Sharing Location:</strong>",
                                        "children": [
                                            {
                                                "content": "This step also follows the previous steps and will not be accessible if any prior step has failed.",
                                                "children": [],
                                                "payload": { "lines": "38,39" }
                                            },
                                            {
                                                "content": "Users can share their current location using either the <strong>/sharelocation</strong> command or the <strong>\"Share Location\"</strong> button, which will retrieve and send their location directly.",
                                                "children": [],
                                                "payload": { "lines": "39,41" }
                                            }
                                        ],
                                        "payload": { "lines": "37,41" }
                                    }
                                ],
                                "payload": { "lines": "36,37" }
                            }
                        ],
                        "payload": { "lines": "6,7" }
                    },
                    {
                        "content": "Additional Features and Functions",
                        "children": [
                            {
                                "content": "Commands",
                                "children": [
                                    {
                                        "content": "<strong>/stop</strong>: Forcefully ends the ongoing processes.",
                                        "children": [],
                                        "payload": { "lines": "44,45" }
                                    },
                                    {
                                        "content": "<strong>/help</strong>: Provides assistance and information about available commands.",
                                        "children": [],
                                        "payload": { "lines": "45,46" }
                                    }
                                ],
                                "payload": { "lines": "43,46" }
                            }
                        ],
                        "payload": { "lines": "42,43" }
                    }
                ]
            }
        );
    </script>
</body>

</html>
    