const http = require('http');
const url = require('url');

const server = http.createServer((req, res) => {
  const parsedUrl = url.parse(req.url, true);
  const path = parsedUrl.pathname;
  if (path.startsWith('/r=')) {
    const originalRequest = path.slice(3);
    const randomNumber = Math.random() * 1000;
    console.log(randomNumber)
    const redirectUrl = `https://linkvertise.com/465552/${randomNumber}/dynamic?r=${originalRequest}`;
    res.writeHead(302, {
      'Location': redirectUrl
    });
    res.end();
  } else {
    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('404 Not Found');
  }
});

const port = 8080;
server.listen(port, () => {
  console.log(`Server is listening at http://localhost:${port}`);
});