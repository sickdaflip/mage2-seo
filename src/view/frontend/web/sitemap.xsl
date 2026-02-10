<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html>
            <head>
                <title>
                    <xsl:choose>
                        <xsl:when test="sitemap:sitemapindex">XML Sitemap Index</xsl:when>
                        <xsl:otherwise>XML Sitemap</xsl:otherwise>
                    </xsl:choose>
                </title>
                <style type="text/css">
                    :root {
                        --primary: #1a1a2e;
                        --secondary: #16213e;
                        --accent: #0f3460;
                        --highlight: #e94560;
                        --text: #333;
                        --light: #f5f5f5;
                        --border: #ddd;
                    }
                    * {
                        box-sizing: border-box;
                    }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
                        font-size: 14px;
                        color: var(--text);
                        background: var(--light);
                        margin: 0;
                        padding: 0;
                        line-height: 1.6;
                    }
                    .header {
                        background: linear-gradient(135deg, var(--primary), var(--accent));
                        color: white;
                        padding: 30px 20px;
                        text-align: center;
                    }
                    .header h1 {
                        margin: 0 0 10px 0;
                        font-size: 28px;
                        font-weight: 600;
                    }
                    .header p {
                        margin: 0;
                        opacity: 0.9;
                        font-size: 15px;
                    }
                    .header a {
                        color: #fff;
                        text-decoration: underline;
                    }
                    .container {
                        max-width: 1200px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .stats {
                        display: flex;
                        gap: 20px;
                        margin-bottom: 20px;
                        flex-wrap: wrap;
                    }
                    .stat-box {
                        background: white;
                        border-radius: 8px;
                        padding: 20px;
                        flex: 1;
                        min-width: 150px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .stat-box .number {
                        font-size: 32px;
                        font-weight: bold;
                        color: var(--highlight);
                    }
                    .stat-box .label {
                        color: #666;
                        font-size: 13px;
                    }
                    table {
                        width: 100%;
                        background: white;
                        border-collapse: collapse;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    th {
                        background: var(--secondary);
                        color: white;
                        padding: 15px;
                        text-align: left;
                        font-weight: 500;
                    }
                    td {
                        padding: 12px 15px;
                        border-bottom: 1px solid var(--border);
                    }
                    tr:last-child td {
                        border-bottom: none;
                    }
                    tr:hover td {
                        background: #f8f9fa;
                    }
                    a {
                        color: var(--accent);
                        text-decoration: none;
                    }
                    a:hover {
                        color: var(--highlight);
                        text-decoration: underline;
                    }
                    .url-cell {
                        word-break: break-all;
                        max-width: 500px;
                    }
                    .priority {
                        text-align: center;
                    }
                    .priority-high {
                        color: #28a745;
                        font-weight: bold;
                    }
                    .priority-medium {
                        color: #ffc107;
                    }
                    .priority-low {
                        color: #6c757d;
                    }
                    .images-count {
                        background: var(--accent);
                        color: white;
                        padding: 2px 8px;
                        border-radius: 12px;
                        font-size: 12px;
                    }
                    .hreflang-list {
                        font-size: 12px;
                        color: #666;
                    }
                    .hreflang-list span {
                        display: inline-block;
                        background: #e9ecef;
                        padding: 2px 6px;
                        border-radius: 3px;
                        margin: 2px;
                    }
                    .footer {
                        text-align: center;
                        padding: 20px;
                        color: #666;
                        font-size: 13px;
                    }
                    @media (max-width: 768px) {
                        .container {
                            padding: 10px;
                        }
                        th, td {
                            padding: 8px;
                            font-size: 12px;
                        }
                        .stats {
                            flex-direction: column;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>
                        <xsl:choose>
                            <xsl:when test="sitemap:sitemapindex">XML Sitemap Index</xsl:when>
                            <xsl:otherwise>XML Sitemap</xsl:otherwise>
                        </xsl:choose>
                    </h1>
                    <p>
                        Generated by <strong>FlipDev SEO</strong> for Magento 2.
                        Learn more at <a href="https://www.sitemaps.org/" target="_blank">sitemaps.org</a>.
                    </p>
                </div>

                <div class="container">
                    <xsl:apply-templates/>
                </div>

                <div class="footer">
                    This XML sitemap is used by search engines to discover and index pages on your website.
                </div>
            </body>
        </html>
    </xsl:template>

    <!-- Sitemap Index Template -->
    <xsl:template match="sitemap:sitemapindex">
        <div class="stats">
            <div class="stat-box">
                <div class="number"><xsl:value-of select="count(sitemap:sitemap)"/></div>
                <div class="label">Sitemaps</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sitemap</th>
                    <th style="width: 200px;">Last Modified</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sitemap:sitemap">
                    <tr>
                        <td class="url-cell">
                            <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                        </td>
                        <td>
                            <xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/>
                            <xsl:text> </xsl:text>
                            <xsl:value-of select="substring(sitemap:lastmod, 12, 5)"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>

    <!-- URL Set Template -->
    <xsl:template match="sitemap:urlset">
        <div class="stats">
            <div class="stat-box">
                <div class="number"><xsl:value-of select="count(sitemap:url)"/></div>
                <div class="label">URLs</div>
            </div>
            <xsl:if test="sitemap:url/image:image">
                <div class="stat-box">
                    <div class="number"><xsl:value-of select="count(sitemap:url/image:image)"/></div>
                    <div class="label">Images</div>
                </div>
            </xsl:if>
        </div>

        <table>
            <thead>
                <tr>
                    <th>URL</th>
                    <xsl:if test="sitemap:url/sitemap:priority">
                        <th style="width: 80px;">Priority</th>
                    </xsl:if>
                    <xsl:if test="sitemap:url/sitemap:changefreq">
                        <th style="width: 100px;">Change Freq</th>
                    </xsl:if>
                    <xsl:if test="sitemap:url/sitemap:lastmod">
                        <th style="width: 120px;">Last Modified</th>
                    </xsl:if>
                    <xsl:if test="sitemap:url/image:image">
                        <th style="width: 80px;">Images</th>
                    </xsl:if>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sitemap:url">
                    <tr>
                        <td class="url-cell">
                            <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                            <xsl:if test="xhtml:link">
                                <div class="hreflang-list">
                                    <xsl:for-each select="xhtml:link[@rel='alternate']">
                                        <span><xsl:value-of select="@hreflang"/></span>
                                    </xsl:for-each>
                                </div>
                            </xsl:if>
                        </td>
                        <xsl:if test="../sitemap:url/sitemap:priority">
                            <td class="priority">
                                <xsl:attribute name="class">
                                    priority
                                    <xsl:choose>
                                        <xsl:when test="sitemap:priority &gt;= 0.8"> priority-high</xsl:when>
                                        <xsl:when test="sitemap:priority &gt;= 0.5"> priority-medium</xsl:when>
                                        <xsl:otherwise> priority-low</xsl:otherwise>
                                    </xsl:choose>
                                </xsl:attribute>
                                <xsl:value-of select="sitemap:priority"/>
                            </td>
                        </xsl:if>
                        <xsl:if test="../sitemap:url/sitemap:changefreq">
                            <td><xsl:value-of select="sitemap:changefreq"/></td>
                        </xsl:if>
                        <xsl:if test="../sitemap:url/sitemap:lastmod">
                            <td><xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/></td>
                        </xsl:if>
                        <xsl:if test="../sitemap:url/image:image">
                            <td style="text-align: center;">
                                <xsl:if test="image:image">
                                    <span class="images-count"><xsl:value-of select="count(image:image)"/></span>
                                </xsl:if>
                            </td>
                        </xsl:if>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>
