<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resistor Color Code Recognition</title>
    <style>
        #videoContainer {
            margin: auto;
            width: 640px;
            height: 480px;
            position: relative;
        }
        #canvasOutput {
            position: absolute;
            top: 0;
            left: 0;
        }
        #finalValue {
            text-align: center;
            margin-top: 20px;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div id="videoContainer">
        <video id="video" autoplay></video>
        <canvas id="canvasOutput" width="640" height="480"></canvas>
    </div>
    <div id="finalValue"></div>

    <script src="https://docs.opencv.org/4.5.3/opencv.js"></script>
    <script>
        // Access webcam
        const video = document.getElementById('video');

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
            })
            .catch(function(err) {
                console.log("An error occurred: " + err);
            });

        // Process video frames
        const canvas = document.getElementById('canvasOutput');
        const ctx = canvas.getContext('2d');
        const finalValueDisplay = document.getElementById('finalValue');

        // Load OpenCV.js
        cv.onRuntimeInitialized = () => {
            setInterval(function() {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                // Convert image data to OpenCV Mat
                const src = cv.matFromArray(imageData.height, imageData.width, cv.CV_8UC4, imageData.data);

                // Convert color image to grayscale
                const gray = new cv.Mat();
                cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

                // Apply Canny edge detection
                const edges = new cv.Mat();
                cv.Canny(gray, edges, 50, 150);

                // Find contours
                const contours = new cv.MatVector();
                const hierarchy = new cv.Mat();
                cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

                // Draw contours (for visualization)
                for (let i = 0; i < contours.size(); ++i) {
                    const color = new cv.Scalar(255, 255, 255);
                    cv.drawContours(src, contours, i, color, 1, cv.LINE_8, hierarchy, 100);
                }

                // Decode resistor value from colors (replace this with your actual decoding logic)
                const finalValue = "47kΩ"; // Example value, replace with actual calculation

                finalValueDisplay.textContent = "Final Value: " + finalValue;

                // Clean up
                src.delete();
                gray.delete();
                edges.delete();
                contours.delete();
                hierarchy.delete();
            }, 1000); // Adjust the interval as needed
        };
    </script>
</body>
</html>
