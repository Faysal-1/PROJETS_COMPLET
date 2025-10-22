@Controller
public class PortfolioController {
    
    @GetMapping("/")
    public String home(Model model) {
        model.addAttribute("pageTitle", "Portfolio - John Doe");
        return "index";
    }

    @PostMapping("/contact")
    public String contactForm(@RequestParam String name,@RequestParam String email,@RequestParam String message) {
        // Traitement du formulaire
        return "redirect:/?success";
    }
}