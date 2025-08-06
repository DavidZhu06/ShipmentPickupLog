const canvas = document.getElementById('signatureCanvas'); /*.getElementbyID is a built-in js method that searches document (whole loaded HTML page) for element with ID attribute equal to signatureCanvas
const canvas: stores that HTML element in constant variable called canvas*/
const ctx = canvas.getContext('2d'); /*Saves 2d drawing context of <canvas> element to ctx variable */

//below variables used to track whether user is drawing and store last touch coordinates 
let isDrawing = false; //means user is not drawing at the start, set to true when user clicks mouse or touches canvas
let lastX = 0;
let lastY = 0;
let signatureData = []; // Array to store signature data if needed
let currentLanguage = 'en'; // Default language

//specify drawing style
ctx.lineWidth = 3;
ctx.lineCap = 'round';
ctx.lineJoin = 'round'; // Smooths line connections
ctx.strokeStyle = '#000';

// Adjust canvas resolution to match CSS size
function resizeCanvas() {
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * window.devicePixelRatio;
  canvas.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  redrawSignature(); // Redraw signature after resize
}

// Redraw signature from stored points
function redrawSignature() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.beginPath();
  signatureData.forEach(segment => {
    ctx.moveTo(segment[0].x, segment[0].y);
    for (let i = 1; i < segment.length; i++) {
      ctx.lineTo(segment[i].x, segment[i].y);
    }
    ctx.stroke();
  });
  ctx.beginPath();
}

// Get canvas coordinates
function getCanvasCoordinates(event) {
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width;
  const scaleY = canvas.height / rect.height;

  let clientX, clientY;
  if (event.type.startsWith('touch')) {
    clientX = event.touches[0].clientX;
    clientY = event.touches[0].clientY;
  } else {
    clientX = event.clientX;
    clientY = event.clientY;
  }

  return {
    x: (clientX - rect.left) * scaleX,
    y: (clientY - rect.top) * scaleY
  };
}


// Start drawing
function startDrawing(e) {
  e.preventDefault();
  isDrawing = true;
  const coords = getCanvasCoordinates(e);
  lastX = coords.x;
  lastY = coords.y;
  signatureData.push([{ x: lastX, y: lastY }]); // Start new segment
  ctx.beginPath();
}

// Draw on canvas
function draw(e) {
  if (!isDrawing) return;
  e.preventDefault();
  const coords = getCanvasCoordinates(e);
  ctx.moveTo(lastX, lastY);
  ctx.lineTo(coords.x, coords.y);
  ctx.stroke();
  signatureData[signatureData.length - 1].push({ x: coords.x, y: coords.y }); // Add to current segment
  lastX = coords.x;
  lastY = coords.y;
}

// Stop drawing
function stopDrawing() {
  isDrawing = false;
  ctx.beginPath();
}

// Event listeners
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);
canvas.addEventListener('touchstart', startDrawing);
canvas.addEventListener('touchmove', draw);
canvas.addEventListener('touchend', stopDrawing);
canvas.addEventListener('touchcancel', stopDrawing);

// Clear canvas
document.getElementById('clearBtn').addEventListener('click', () => {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  signatureData = []; // Clear stored points
  document.getElementById('signature').value = '';
});

// Update signature input before form submission
document.getElementById('submitBtn').addEventListener('click', () => {
  if (signatureData.length > 0) {
    const signature = canvas.toDataURL('image/png');
    document.getElementById('signature').value = signature;
  } else {
    alert('Please provide a signature before submitting.');
  }
});


// Resize canvas on load and window resize
window.addEventListener('resize', resizeCanvas);
resizeCanvas();