import { expect, Locator, Page } from "@playwright/test";

export default class CheckoutSuccessPage {
    readonly page: Page;
    readonly checkoutSuccessWrapper: Locator;

    constructor(page: Page) {
        this.page = page;
        this.checkoutSuccessWrapper = page.locator(".checkout-success");
    }

    async extractOrderNumber(): Promise<string> {
        const orderSuccessString = await this.checkoutSuccessWrapper.textContent() || "";
        const regex = orderSuccessString.match(/\d+/);

        expect(regex, "Unable to extract the Order Number from the Checkout Success page").not.toBeNull();

        return regex[0];
    }
}
