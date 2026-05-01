import express from "express";
import { createServer as createViteServer } from "vite";
import path from "path";
import { fileURLToPath } from "url";
import Parser from "rss-parser";
import cors from "cors";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function startServer() {
  const app = express();
  const PORT = 3000;
  const parser = new Parser();

  app.use(cors());
  app.use(express.json());

  // API to fetch Echorouk RSS feed
  app.get("/api/rss", async (req, res) => {
    try {
      const feed = await parser.parseURL("https://www.echoroukonline.com/feed");
      res.json(feed);
    } catch (error) {
      console.error("RSS fetch error:", error);
      res.status(500).json({ error: "Failed to fetch RSS feed" });
    }
  });

  // API to trigger "Manual Sync" (Simulated)
  app.post("/api/sync", async (req, res) => {
    // In a real WP plugin, this would write to DB and download images.
    // Here we just simulate success.
    try {
      // Simulate small delay
      await new Promise(resolve => setTimeout(resolve, 2000));
      res.json({ message: "Sync completed successfully!", count: 10 });
    } catch (error) {
      res.status(500).json({ error: "Sync failed" });
    }
  });

  // Vite middleware for development
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(__dirname, "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running at http://0.0.0.0:${PORT}`);
  });
}

startServer();
