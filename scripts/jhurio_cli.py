#!/usr/bin/env python3
"""
Jhurio Store CLI Tool
Universal Command Line Interface for managing live store data on https://jhurio.com
Zero external dependencies (uses standard library urllib, json, sys, os, argparse).
"""

import sys
import os
import json
import argparse
import urllib.request
import urllib.error
import urllib.parse
from typing import Optional, Dict, Any, List

DEFAULT_URL = os.environ.get("JHURIO_API_URL", "https://jhurio.com")
DEFAULT_TOKEN = os.environ.get("JHURIO_API_TOKEN", "prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c")

# ANSI Color formatting
C_RESET = "\033[0m"
C_BOLD = "\033[1m"
C_GREEN = "\033[32m"
C_BLUE = "\033[34m"
C_CYAN = "\033[36m"
C_YELLOW = "\033[33m"
C_RED = "\033[31m"
C_MAGENTA = "\033[35m"

class JhurioClient:
    def __init__(self, base_url: str = DEFAULT_URL, token: str = DEFAULT_TOKEN, verbose: bool = False, json_output: bool = False):
        self.base_url = base_url.rstrip("/")
        self.token = token
        self.verbose = verbose
        self.json_output = json_output

    def request(self, method: str, endpoint: str, data: Optional[Dict[str, Any]] = None, params: Optional[Dict[str, Any]] = None, raise_error: bool = False) -> Any:
        url = f"{self.base_url}{endpoint}"
        if params:
            query_str = urllib.parse.urlencode({k: v for k, v in params.items() if v is not None})
            if query_str:
                url += f"?{query_str}"

        headers = {
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json",
            "User-Agent": "Jhurio-CLI/2.0",
        }

        body_bytes = None
        if data is not None:
            headers["Content-Type"] = "application/json"
            body_bytes = json.dumps(data).encode("utf-8")

        if self.verbose:
            print(f"{C_CYAN}[REQUEST]{C_RESET} {method} {url}", file=sys.stderr)
            if data:
                print(f"{C_CYAN}[BODY]{C_RESET} {json.dumps(data, indent=2)}", file=sys.stderr)

        req = urllib.request.Request(url, data=body_bytes, headers=headers, method=method)
        try:
            with urllib.request.urlopen(req, timeout=30) as response:
                res_body = response.read().decode("utf-8")
                if not res_body:
                    return {}
                return json.loads(res_body)
        except urllib.error.HTTPError as e:
            if raise_error:
                raise e
            err_body = e.read().decode("utf-8")
            try:
                err_json = json.loads(err_body)
            except Exception:
                err_json = {"error": err_body}
            if self.json_output:
                print(json.dumps({"status_code": e.code, "error": err_json}, indent=2))
            else:
                print(f"{C_RED}HTTP Error {e.code}:{C_RESET} {err_json.get('error') or err_json.get('message') or err_json}", file=sys.stderr)
            sys.exit(1)
        except urllib.error.URLError as e:
            if self.json_output:
                print(json.dumps({"error": str(e.reason)}, indent=2))
            else:
                print(f"{C_RED}Connection Error:{C_RESET} {e.reason}", file=sys.stderr)
            sys.exit(1)

    def print_result(self, data: Any, title: Optional[str] = None):
        if self.json_output:
            print(json.dumps(data, indent=2, ensure_ascii=False))
            return

        if title:
            print(f"\n{C_BOLD}{C_GREEN}=== {title} ==={C_RESET}")
        print(json.dumps(data, indent=2, ensure_ascii=False))


def parse_data_input(val: Optional[str]) -> Dict[str, Any]:
    if not val:
        return {}
    if os.path.isfile(val):
        with open(val, "r", encoding="utf-8") as f:
            return json.load(f)
    try:
        return json.loads(val)
    except Exception as e:
        print(f"{C_RED}Invalid JSON data provided:{C_RESET} {e}", file=sys.stderr)
        sys.exit(1)


# ----------------------------------------------------------------------
# Command Handlers
# ----------------------------------------------------------------------

def cmd_ping(client: JhurioClient, args):
    res = client.request("GET", "/api/ping")
    if client.json_output:
        print(json.dumps(res, indent=2))
    else:
        print(f"{C_GREEN}✓ Connected successfully to {client.base_url}{C_RESET}")
        print(f"Status:      {res.get('status')}")
        print(f"Server Time: {res.get('server_time')}")
        print(f"API Version: {res.get('api_version')}")


def cmd_stats(client: JhurioClient, args):
    res = client.request("POST", "/api/cli", data={"action": "stats"})
    if client.json_output:
        print(json.dumps(res, indent=2))
        return

    print(f"\n{C_BOLD}{C_MAGENTA}🛒 JHURIO STORE DASHBOARD METRICS{C_RESET}")
    print(f"{C_BOLD}{'='*40}{C_RESET}")
    prod = res.get("products", {})
    orders = res.get("orders", {})
    rev = res.get("reviews", {})
    print(f"{C_CYAN}Products:{C_RESET}    Total: {prod.get('total')} | Published: {prod.get('published')} | Low Stock: {prod.get('low_stock')}")
    print(f"{C_CYAN}Orders:{C_RESET}      Total: {orders.get('total')} | Pending: {C_YELLOW}{orders.get('pending')}{C_RESET} | Delivered: {orders.get('delivered')}")
    print(f"{C_CYAN}Revenue:{C_RESET}     ৳ {orders.get('revenue', 0):,.2f}")
    print(f"{C_CYAN}Categories:{C_RESET}  {res.get('categories')}")
    print(f"{C_CYAN}Reviews:{C_RESET}     Total: {rev.get('total')} | Approved: {rev.get('approved')} | Pending: {rev.get('pending')}")
    print(f"{C_CYAN}Banners:{C_RESET}     {res.get('banners')} banners | {res.get('homepage_sections')} homepage sections")
    print(f"{C_BOLD}{'='*40}{C_RESET}\n")


# ------------------ Products ------------------

def cmd_products_list(client: JhurioClient, args):
    params = {}
    if args.status:
        params["status"] = args.status
    if args.search:
        params["search"] = args.search
    if args.category:
        params["category"] = args.category
    if args.limit:
        params["limit"] = args.limit

    try:
        res = client.request("GET", "/api/products", params=params, raise_error=True)
    except urllib.error.HTTPError as e:
        if e.code in [404, 405]:
            # Gracefully fallback for legacy live endpoints
            res = client.request("POST", "/api/products", data={"action": "list_products"})
        else:
            raise e

    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    products = res.get("products", [])
    total = res.get("total", len(products))
    print(f"\n{C_BOLD}Total Products: {total}{C_RESET}\n")
    print(f"{C_BOLD}{'ID':<5} {'PRICE':<10} {'STOCK':<8} {'STATUS':<12} {'NAME'}{C_RESET}")
    print("-" * 75)
    for p in products:
        status_col = C_GREEN if p.get('status') == 'published' else C_YELLOW
        print(f"{p.get('id'):<5} ৳{float(p.get('price', 0)):<9.2f} {p.get('stock_quantity', 0):<8} {status_col}{p.get('status', 'draft'):<12}{C_RESET} {p.get('name')[:40]}")
    print()


def cmd_products_get(client: JhurioClient, args):
    res = client.request("GET", f"/api/products/{args.id_or_slug}")
    client.print_result(res, f"Product Details: {args.id_or_slug}")


def cmd_products_create(client: JhurioClient, args):
    payload = parse_data_input(args.data)
    if args.name:
        payload["name"] = args.name
    if args.price is not None:
        payload["price"] = args.price
    if args.compare_at_price is not None:
        payload["compare_at_price"] = args.compare_at_price
    if args.stock is not None:
        payload["stock_quantity"] = args.stock
    if args.status:
        payload["status"] = args.status
    if args.slug:
        payload["slug"] = args.slug

    res = client.request("POST", "/api/products", data=payload)
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
    else:
        p = res.get("product", {})
        print(f"{C_GREEN}✓ Product created successfully!{C_RESET}")
        print(f"ID:    {p.get('id')}")
        print(f"Name:  {p.get('name')}")
        print(f"Slug:  {p.get('slug')}")
        print(f"Price: ৳{p.get('price')}")
        print(f"Live:  {client.base_url}/product/{p.get('slug')}")


def cmd_products_update(client: JhurioClient, args):
    payload = parse_data_input(args.data)
    if args.name:
        payload["name"] = args.name
    if args.price is not None:
        payload["price"] = args.price
    if args.compare_at_price is not None:
        payload["compare_at_price"] = args.compare_at_price
    if args.stock is not None:
        payload["stock_quantity"] = args.stock
    if args.status:
        payload["status"] = args.status

    res = client.request("PUT", f"/api/products/{args.id_or_slug}", data=payload)
    client.print_result(res, f"Product Updated: {args.id_or_slug}")


def cmd_products_delete(client: JhurioClient, args):
    res = client.request("DELETE", f"/api/products/{args.id_or_slug}")
    if client.json_output:
        print(json.dumps(res, indent=2))
    else:
        print(f"{C_GREEN}✓ Product {args.id_or_slug} deleted successfully.{C_RESET}")


def cmd_products_stock(client: JhurioClient, args):
    res = client.request("POST", "/api/stock/adjust", data={"product_id": args.product_id, "stock_quantity": args.quantity})
    if client.json_output:
        print(json.dumps(res, indent=2))
    else:
        print(f"{C_GREEN}✓ Stock for Product #{args.product_id} updated to {args.quantity}{C_RESET}")


# ------------------ Categories ------------------

def cmd_categories_list(client: JhurioClient, args):
    params = {}
    if args.search:
        params["search"] = args.search
    res = client.request("GET", "/api/categories", params=params)
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    cats = res.get("categories", [])
    print(f"\n{C_BOLD}{'ID':<5} {'PRODUCTS':<10} {'SLUG':<30} {'NAME'}{C_RESET}")
    print("-" * 65)
    for c in cats:
        print(f"{c.get('id'):<5} {c.get('products_count', 0):<10} {c.get('slug', ''):<30} {c.get('name')}")
    print()


def cmd_categories_create(client: JhurioClient, args):
    data = {"name": args.name}
    if args.slug:
        data["slug"] = args.slug
    if args.description:
        data["description"] = args.description
    res = client.request("POST", "/api/categories", data=data)
    client.print_result(res, "Category Created")


def cmd_categories_delete(client: JhurioClient, args):
    res = client.request("DELETE", f"/api/categories/{args.id_or_slug}")
    client.print_result(res, "Category Deleted")


# ------------------ Orders ------------------

def cmd_orders_list(client: JhurioClient, args):
    params = {}
    if args.status:
        params["status"] = args.status
    if args.search:
        params["search"] = args.search
    if args.limit:
        params["limit"] = args.limit

    res = client.request("GET", "/api/orders", params=params)
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    orders = res.get("orders", [])
    print(f"\n{C_BOLD}Total Orders: {res.get('total')}{C_RESET}\n")
    print(f"{C_BOLD}{'ID':<6} {'STATUS':<12} {'CUSTOMER':<22} {'PHONE':<15} {'TOTAL':<10}{C_RESET}")
    print("-" * 70)
    for o in orders:
        st = o.get("status")
        col = C_YELLOW if st == "pending" else (C_GREEN if st in ["delivered", "completed"] else C_BLUE)
        print(f"{o.get('id'):<6} {col}{st:<12}{C_RESET} {o.get('name', 'N/A')[:20]:<22} {o.get('phone', 'N/A'):<15} ৳{float(o.get('total', 0)):<9.2f}")
    print()


def cmd_orders_get(client: JhurioClient, args):
    res = client.request("GET", f"/api/orders/{args.order_id}")
    client.print_result(res, f"Order #{args.order_id} Details")


def cmd_orders_status(client: JhurioClient, args):
    data = {"status": args.status}
    if args.note:
        data["note"] = args.note
    res = client.request("PUT", f"/api/orders/{args.order_id}", data=data)
    client.print_result(res, f"Order #{args.order_id} Status Updated to {args.status}")


# ------------------ Reviews ------------------

def cmd_reviews_list(client: JhurioClient, args):
    params = {}
    if args.product_id:
        params["product_id"] = args.product_id
    if args.status:
        params["status"] = args.status
    if args.limit:
        params["limit"] = args.limit

    res = client.request("GET", "/api/reviews", params=params)
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    reviews = res.get("reviews", [])
    print(f"\n{C_BOLD}Total Reviews: {res.get('total')}{C_RESET}\n")
    print(f"{C_BOLD}{'ID':<5} {'PRODUCT':<10} {'RATING':<8} {'REVIEWER':<18} {'STATUS':<10} {'COMMENT'}{C_RESET}")
    print("-" * 80)
    for r in reviews:
        col = C_GREEN if r.get('status') == 'approved' else C_YELLOW
        comm = (r.get('comment') or '')[:30]
        print(f"{r.get('id'):<5} #{r.get('product_id'):<9} {'★'*int(r.get('rating', 5)):<8} {r.get('reviewer_name', '')[:16]:<18} {col}{r.get('status'):<10}{C_RESET} {comm}")
    print()


def cmd_reviews_add(client: JhurioClient, args):
    data = {
        "product_id": args.product_id,
        "reviewer_name": args.name,
        "rating": args.rating,
        "comment": args.comment,
        "status": args.status or "approved",
    }
    if args.email:
        data["reviewer_email"] = args.email
    res = client.request("POST", "/api/reviews", data=data)
    client.print_result(res, "Review Added")


def cmd_reviews_delete(client: JhurioClient, args):
    res = client.request("DELETE", f"/api/reviews/{args.review_id}")
    client.print_result(res, "Review Deleted")


# ------------------ Banners ------------------

def cmd_banners_list(client: JhurioClient, args):
    params = {}
    if args.type:
        params["type"] = args.type
    res = client.request("GET", "/api/banners", params=params)
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    banners = res.get("banners", [])
    print(f"\n{C_BOLD}Total Banners: {len(banners)}{C_RESET}\n")
    print(f"{C_BOLD}{'ID':<5} {'TYPE':<16} {'ORDER':<7} {'ACTIVE':<8} {'TITLE':<25} {'IMAGE URL'}{C_RESET}")
    print("-" * 90)
    for b in banners:
        media_url = (b.get('media') or {}).get('url', 'N/A')
        print(f"{b.get('id'):<5} {b.get('type'):<16} {b.get('order', 0):<7} {str(b.get('is_active')):<8} {(b.get('title') or 'Untitled')[:23]:<25} {media_url[:40]}")
    print()


def cmd_banners_create(client: JhurioClient, args):
    data = {
        "type": args.type,
        "title": args.title,
        "link_url": args.link,
    }
    if args.image_url:
        data["image_url"] = args.image_url
    if args.media_id:
        data["media_id"] = args.media_id
    res = client.request("POST", "/api/banners", data=data)
    client.print_result(res, "Banner Created")


def cmd_banners_delete(client: JhurioClient, args):
    res = client.request("DELETE", f"/api/banners/{args.banner_id}")
    client.print_result(res, "Banner Deleted")


# ------------------ Homepage ------------------

def cmd_homepage_list(client: JhurioClient, args):
    res = client.request("GET", "/api/homepage-sections")
    if client.json_output:
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return

    sections = res.get("sections", [])
    print(f"\n{C_BOLD}{'ID':<5} {'TYPE':<16} {'ORDER':<7} {'ACTIVE':<8} {'TITLE'}{C_RESET}")
    print("-" * 60)
    for s in sections:
        print(f"{s.get('id'):<5} {s.get('type'):<16} {s.get('order', 0):<7} {str(s.get('is_active')):<8} {s.get('title') or '(none)'}")
    print()


def cmd_homepage_get(client: JhurioClient, args):
    res = client.request("GET", f"/api/homepage-sections/{args.id_or_type}")
    client.print_result(res, f"Homepage Section: {args.id_or_type}")


def cmd_homepage_hero(client: JhurioClient, args):
    data = {}
    if args.slider_image:
        data["slider_image"] = args.slider_image
    if args.slider_link:
        data["slider_link"] = args.slider_link
    if args.right_top_image:
        data["right_top_image"] = args.right_top_image
    if args.right_top_link:
        data["right_top_link"] = args.right_top_link
    if args.right_bottom_image:
        data["right_bottom_image"] = args.right_bottom_image
    if args.right_bottom_link:
        data["right_bottom_link"] = args.right_bottom_link
    if args.bottom_strip_image:
        data["bottom_strip_image"] = args.bottom_strip_image
    if args.bottom_strip_link:
        data["bottom_strip_link"] = args.bottom_strip_link

    res = client.request("POST", "/api/homepage-sections/hero", data=data)
    client.print_result(res, "Hero Section Banners Updated")


def cmd_homepage_update(client: JhurioClient, args):
    payload = parse_data_input(args.data)
    res = client.request("PUT", f"/api/homepage-sections/{args.id_or_type}", data=payload)
    client.print_result(res, f"Homepage Section #{args.id_or_type} Updated")


# ------------------ Settings ------------------

def cmd_settings_list(client: JhurioClient, args):
    params = {}
    if args.keys:
        params["keys"] = args.keys
    res = client.request("GET", "/api/settings", params=params)
    client.print_result(res.get("settings", res), "Site Settings")


def cmd_settings_get(client: JhurioClient, args):
    res = client.request("GET", f"/api/settings/{args.key}")
    if client.json_output:
        print(json.dumps(res, indent=2))
    else:
        print(f"{C_BOLD}{args.key}:{C_RESET} {res.get('value')}")


def cmd_settings_set(client: JhurioClient, args):
    data = {"settings": {args.key: args.value}}
    res = client.request("POST", "/api/settings", data=data)
    client.print_result(res, f"Setting '{args.key}' Updated")


# ------------------ Media ------------------

def cmd_media_list(client: JhurioClient, args):
    params = {}
    if args.limit:
        params["limit"] = args.limit
    res = client.request("GET", "/api/media", params=params)
    client.print_result(res, "Media Library")


def cmd_media_upload(client: JhurioClient, args):
    data = {}
    if args.url:
        data["image_url"] = args.url
    res = client.request("POST", "/api/media", data=data)
    client.print_result(res, "Media Uploaded")


# ------------------ Raw Request ------------------

def cmd_raw(client: JhurioClient, args):
    data = parse_data_input(args.data) if args.data else None
    res = client.request(args.method.upper(), args.endpoint, data=data)
    print(json.dumps(res, indent=2, ensure_ascii=False))


# ----------------------------------------------------------------------
# Main Argument Parser
# ----------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="Jhurio CLI - Command Line Tool for https://jhurio.com Live Site API",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("--url", default=DEFAULT_URL, help=f"API Base URL (default: {DEFAULT_URL})")
    parser.add_argument("--token", default=DEFAULT_TOKEN, help="API Bearer Token")
    parser.add_argument("--json", action="store_true", help="Output raw JSON instead of formatted text")
    parser.add_argument("-v", "--verbose", action="store_true", help="Print debug HTTP requests")

    subparsers = parser.add_subparsers(dest="subcommand", required=True, help="Subcommand to execute")

    # ping
    sub_ping = subparsers.add_parser("ping", help="Check API connection status")
    sub_ping.set_defaults(func=cmd_ping)

    # stats
    sub_stats = subparsers.add_parser("stats", help="Get live dashboard metrics")
    sub_stats.set_defaults(func=cmd_stats)

    # products
    sub_prod = subparsers.add_parser("products", help="Manage products")
    prod_subs = sub_prod.add_subparsers(dest="action", required=True)

    p_list = prod_subs.add_parser("list", help="List products")
    p_list.add_argument("--status", choices=["published", "draft", "out_of_stock"], help="Filter by status")
    p_list.add_argument("--search", help="Search keyword in name or slug")
    p_list.add_argument("--category", help="Filter by category slug or name")
    p_list.add_argument("--limit", type=int, default=20, help="Number of products to show")
    p_list.set_defaults(func=cmd_products_list)

    p_get = prod_subs.add_parser("get", help="Get product details")
    p_get.add_argument("id_or_slug", help="Product ID or Slug")
    p_get.set_defaults(func=cmd_products_get)

    p_create = prod_subs.add_parser("create", help="Create new product")
    p_create.add_argument("--data", help="JSON string or file path with product attributes")
    p_create.add_argument("--name", help="Product Name")
    p_create.add_argument("--price", type=float, help="Selling Price")
    p_create.add_argument("--compare-at-price", type=float, help="Compare at / Original Price")
    p_create.add_argument("--stock", type=int, help="Stock quantity")
    p_create.add_argument("--status", choices=["published", "draft"], help="Product status")
    p_create.add_argument("--slug", help="Custom Slug")
    p_create.set_defaults(func=cmd_products_create)

    p_update = prod_subs.add_parser("update", help="Update existing product")
    p_update.add_argument("id_or_slug", help="Product ID or Slug")
    p_update.add_argument("--data", help="JSON string or file path with attributes to update")
    p_update.add_argument("--name", help="Product Name")
    p_update.add_argument("--price", type=float, help="Selling Price")
    p_update.add_argument("--compare-at-price", type=float, help="Compare at / Original Price")
    p_update.add_argument("--stock", type=int, help="Stock quantity")
    p_update.add_argument("--status", choices=["published", "draft", "out_of_stock"], help="Product status")
    p_update.set_defaults(func=cmd_products_update)

    p_del = prod_subs.add_parser("delete", help="Delete product")
    p_del.add_argument("id_or_slug", help="Product ID or Slug")
    p_del.set_defaults(func=cmd_products_delete)

    p_stock = prod_subs.add_parser("set-stock", help="Quick adjust stock quantity")
    p_stock.add_argument("product_id", type=int, help="Product ID")
    p_stock.add_argument("quantity", type=int, help="New stock quantity")
    p_stock.set_defaults(func=cmd_products_stock)

    # categories
    sub_cat = subparsers.add_parser("categories", help="Manage categories")
    cat_subs = sub_cat.add_subparsers(dest="action", required=True)

    c_list = cat_subs.add_parser("list", help="List all categories")
    c_list.add_argument("--search", help="Search keyword")
    c_list.set_defaults(func=cmd_categories_list)

    c_create = cat_subs.add_parser("create", help="Create new category")
    c_create.add_argument("--name", required=True, help="Category Name")
    c_create.add_argument("--slug", help="Category Slug")
    c_create.add_argument("--description", help="Description")
    c_create.set_defaults(func=cmd_categories_create)

    c_del = cat_subs.add_parser("delete", help="Delete category")
    c_del.add_argument("id_or_slug", help="Category ID or Slug")
    c_del.set_defaults(func=cmd_categories_delete)

    # orders
    sub_ord = subparsers.add_parser("orders", help="Manage orders")
    ord_subs = sub_ord.add_subparsers(dest="action", required=True)

    o_list = ord_subs.add_parser("list", help="List orders")
    o_list.add_argument("--status", choices=["pending", "processing", "shipped", "delivered", "completed", "cancelled"])
    o_list.add_argument("--search", help="Search customer name, phone, or order ID")
    o_list.add_argument("--limit", type=int, default=20)
    o_list.set_defaults(func=cmd_orders_list)

    o_get = ord_subs.add_parser("get", help="Get order details")
    o_get.add_argument("order_id", type=int, help="Order ID")
    o_get.set_defaults(func=cmd_orders_get)

    o_status = ord_subs.add_parser("set-status", help="Update order status")
    o_status.add_argument("order_id", type=int, help="Order ID")
    o_status.add_argument("status", choices=["pending", "processing", "shipped", "delivered", "completed", "cancelled"])
    o_status.add_argument("--note", help="Order note or log description")
    o_status.set_defaults(func=cmd_orders_status)

    # reviews
    sub_rev = subparsers.add_parser("reviews", help="Manage reviews")
    rev_subs = sub_rev.add_subparsers(dest="action", required=True)

    r_list = rev_subs.add_parser("list", help="List reviews")
    r_list.add_argument("--product-id", type=int)
    r_list.add_argument("--status", choices=["approved", "pending", "spam"])
    r_list.add_argument("--limit", type=int, default=50)
    r_list.set_defaults(func=cmd_reviews_list)

    r_add = rev_subs.add_parser("add", help="Add review")
    r_add.add_argument("--product-id", type=int, required=True)
    r_add.add_argument("--name", required=True, help="Reviewer Name")
    r_add.add_argument("--rating", type=int, default=5)
    r_add.add_argument("--comment", required=True)
    r_add.add_argument("--email", help="Reviewer Email")
    r_add.add_argument("--status", choices=["approved", "pending", "spam"], default="approved")
    r_add.set_defaults(func=cmd_reviews_add)

    r_del = rev_subs.add_parser("delete", help="Delete review")
    r_del.add_argument("review_id", type=int)
    r_del.set_defaults(func=cmd_reviews_delete)

    # banners
    sub_ban = subparsers.add_parser("banners", help="Manage banners")
    ban_subs = sub_ban.add_subparsers(dest="action", required=True)

    b_list = ban_subs.add_parser("list", help="List banners")
    b_list.add_argument("--type", choices=["slider", "right_top", "right_bottom", "bottom_strip"])
    b_list.set_defaults(func=cmd_banners_list)

    b_create = ban_subs.add_parser("create", help="Create banner")
    b_create.add_argument("--type", required=True, choices=["slider", "right_top", "right_bottom", "bottom_strip"])
    b_create.add_argument("--image-url", help="Public URL of the banner image")
    b_create.add_argument("--media-id", type=int, help="Existing Media ID")
    b_create.add_argument("--title", help="Banner Title")
    b_create.add_argument("--link", help="Target URL /shop")
    b_create.set_defaults(func=cmd_banners_create)

    b_del = ban_subs.add_parser("delete", help="Delete banner")
    b_del.add_argument("banner_id", type=int)
    b_del.set_defaults(func=cmd_banners_delete)

    # homepage
    sub_home = subparsers.add_parser("homepage", help="Manage homepage builder sections")
    home_subs = sub_home.add_subparsers(dest="action", required=True)

    h_list = home_subs.add_parser("list", help="List sections")
    h_list.set_defaults(func=cmd_homepage_list)

    h_get = home_subs.add_parser("get", help="Get section details")
    h_get.add_argument("id_or_type", help="Section ID or Type (e.g. hero)")
    h_get.set_defaults(func=cmd_homepage_get)

    h_hero = home_subs.add_parser("set-hero", help="Quick update hero section banners")
    h_hero.add_argument("--slider-image", help="Main Slider Image URL")
    h_hero.add_argument("--slider-link", help="Main Slider Link")
    h_hero.add_argument("--right-top-image", help="Right Top Banner Image URL")
    h_hero.add_argument("--right-top-link", help="Right Top Banner Link")
    h_hero.add_argument("--right-bottom-image", help="Right Bottom Banner Image URL")
    h_hero.add_argument("--right-bottom-link", help="Right Bottom Banner Link")
    h_hero.add_argument("--bottom-strip-image", help="Bottom Strip Image URL")
    h_hero.add_argument("--bottom-strip-link", help="Bottom Strip Link")
    h_hero.set_defaults(func=cmd_homepage_hero)

    h_update = home_subs.add_parser("update", help="Update homepage section JSON data")
    h_update.add_argument("id_or_type", help="Section ID or Type")
    h_update.add_argument("--data", required=True, help="JSON string or file path")
    h_update.set_defaults(func=cmd_homepage_update)

    # settings
    sub_set = subparsers.add_parser("settings", help="Manage site settings")
    set_subs = sub_set.add_subparsers(dest="action", required=True)

    s_list = set_subs.add_parser("list", help="List all settings")
    s_list.add_argument("--keys", help="Comma-separated list of keys to fetch")
    s_list.set_defaults(func=cmd_settings_list)

    s_get = set_subs.add_parser("get", help="Get single setting")
    s_get.add_argument("key", help="Setting key")
    s_get.set_defaults(func=cmd_settings_get)

    s_set = set_subs.add_parser("set", help="Set single setting")
    s_set.add_argument("key", help="Setting key")
    s_set.add_argument("value", help="Setting value")
    s_set.set_defaults(func=cmd_settings_set)

    # media
    sub_med = subparsers.add_parser("media", help="Manage media library")
    med_subs = sub_med.add_subparsers(dest="action", required=True)

    m_list = med_subs.add_parser("list", help="List media")
    m_list.add_argument("--limit", type=int, default=24)
    m_list.set_defaults(func=cmd_media_list)

    m_up = med_subs.add_parser("upload", help="Upload media from URL")
    m_up.add_argument("--url", required=True, help="Image URL to download and save into media")
    m_up.set_defaults(func=cmd_media_upload)

    # raw
    sub_raw = subparsers.add_parser("raw", help="Send arbitrary raw API request")
    sub_raw.add_argument("method", choices=["GET", "POST", "PUT", "PATCH", "DELETE"])
    sub_raw.add_argument("endpoint", help="API path, e.g. /api/products")
    sub_raw.add_argument("--data", help="JSON payload or file path")
    sub_raw.set_defaults(func=cmd_raw)

    args = parser.parse_args()

    client = JhurioClient(
        base_url=args.url,
        token=args.token,
        verbose=args.verbose,
        json_output=args.json,
    )

    args.func(client, args)


if __name__ == "__main__":
    main()
